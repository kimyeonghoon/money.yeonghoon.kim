# 템플릿 확장 가이드

> 이 문서는 fullstack_template에 자주 필요한 기능들을 추가하는 방법을 단계별로 안내합니다.
> 모든 확장은 **TDD 방식**으로 진행하며, **Docker 환경**에서 작업합니다.

## 📑 목차

1. [파일 업로드 (AWS S3/MinIO)](#1-파일-업로드-aws-s3minio)
2. [이메일 인증](#2-이메일-인증)
3. [비밀번호 재설정](#3-비밀번호-재설정)
4. [소셜 로그인 (OAuth 2.0)](#4-소셜-로그인-oauth-20)
5. [페이지네이션](#5-페이지네이션)
6. [검색/필터링](#6-검색필터링)
7. [푸시 알림 (FCM)](#7-푸시-알림-fcm)
8. [에러 트래킹 (Sentry)](#8-에러-트래킹-sentry)
9. [Admin 대시보드](#9-admin-대시보드)
10. [Redis 캐싱](#10-redis-캐싱)
11. [Celery 배치 작업](#11-celery-배치-작업)

---

## 1. 파일 업로드 (AWS S3/MinIO)

### 📌 개요
프로필 이미지, 게시물 첨부파일 등 거의 모든 앱에 필요한 기능입니다.

**사용 사례**: 프로필 사진, 게시물 이미지, PDF 첨부, 동영상 업로드

### 📦 필요한 패키지

```bash
# backend/requirements.txt에 추가
boto3==1.35.0  # AWS S3 SDK
Pillow==11.0.0  # 이미지 리사이징
python-multipart==0.0.20  # 이미 있음 (파일 업로드)
```

### 🔧 환경 변수

```bash
# backend/.env에 추가
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_S3_BUCKET_NAME=your-bucket-name
AWS_REGION=ap-northeast-2  # 서울 리전
```

### 🧪 TDD 구현 단계

#### Step 1: 테스트 작성 (Red)

```python
# backend/tests/test_upload.py
import pytest
from fastapi import UploadFile
from io import BytesIO
from PIL import Image


def test_upload_profile_image(client, test_user_token):
    """프로필 이미지 업로드 성공"""
    # Given: 100x100 PNG 이미지 생성
    img = Image.new('RGB', (100, 100), color='red')
    img_bytes = BytesIO()
    img.save(img_bytes, format='PNG')
    img_bytes.seek(0)

    # When: 업로드 요청
    response = client.post(
        "/api/v1/users/me/profile-image",
        headers={"Authorization": f"Bearer {test_user_token}"},
        files={"file": ("test.png", img_bytes, "image/png")}
    )

    # Then: 200 응답, S3 URL 반환
    assert response.status_code == 200
    data = response.json()
    assert "url" in data
    assert data["url"].startswith("https://")


def test_upload_invalid_file_type(client, test_user_token):
    """잘못된 파일 타입 업로드 실패"""
    # Given: .exe 파일
    file_content = BytesIO(b"fake executable")

    # When: 업로드 시도
    response = client.post(
        "/api/v1/users/me/profile-image",
        headers={"Authorization": f"Bearer {test_user_token}"},
        files={"file": ("virus.exe", file_content, "application/x-msdownload")}
    )

    # Then: 400 에러
    assert response.status_code == 400
    assert "Invalid file type" in response.json()["detail"]


def test_upload_file_too_large(client, test_user_token):
    """파일 크기 초과 (5MB 제한)"""
    # Given: 6MB 파일
    large_file = BytesIO(b"0" * (6 * 1024 * 1024))

    # When: 업로드 시도
    response = client.post(
        "/api/v1/users/me/profile-image",
        headers={"Authorization": f"Bearer {test_user_token}"},
        files={"file": ("large.png", large_file, "image/png")}
    )

    # Then: 413 에러
    assert response.status_code == 413
```

#### Step 2: S3 유틸리티 구현 (Green)

```python
# backend/app/core/storage.py
import boto3
from botocore.exceptions import ClientError
from typing import BinaryIO, Optional
from app.config import settings
import uuid
from datetime import datetime


ALLOWED_IMAGE_TYPES = {"image/jpeg", "image/png", "image/gif", "image/webp"}
MAX_FILE_SIZE = 5 * 1024 * 1024  # 5MB


class S3Storage:
    """AWS S3 파일 스토리지 클래스

    Args:
        bucket_name: S3 버킷 이름
        region: AWS 리전
    """

    def __init__(self, bucket_name: str, region: str):
        self.bucket_name = bucket_name
        self.s3_client = boto3.client(
            's3',
            region_name=region,
            aws_access_key_id=settings.AWS_ACCESS_KEY_ID,
            aws_secret_access_key=settings.AWS_SECRET_ACCESS_KEY
        )

    def upload_file(
        self,
        file: BinaryIO,
        content_type: str,
        folder: str = "uploads"
    ) -> str:
        """파일을 S3에 업로드하고 URL 반환

        Args:
            file: 업로드할 파일 객체
            content_type: MIME 타입
            folder: S3 내 폴더명

        Returns:
            업로드된 파일의 공개 URL

        Raises:
            ValueError: 잘못된 파일 타입 또는 크기 초과
            ClientError: S3 업로드 실패
        """
        if content_type not in ALLOWED_IMAGE_TYPES:
            raise ValueError(f"Invalid file type: {content_type}")

        file.seek(0, 2)  # 파일 끝으로 이동
        file_size = file.tell()
        file.seek(0)  # 다시 처음으로

        if file_size > MAX_FILE_SIZE:
            raise ValueError(f"File too large: {file_size} bytes (max {MAX_FILE_SIZE})")

        timestamp = datetime.utcnow().strftime("%Y%m%d_%H%M%S")
        file_id = uuid.uuid4().hex[:8]
        extension = content_type.split("/")[1]
        file_key = f"{folder}/{timestamp}_{file_id}.{extension}"

        self.s3_client.upload_fileobj(
            file,
            self.bucket_name,
            file_key,
            ExtraArgs={"ContentType": content_type, "ACL": "public-read"}
        )

        return f"https://{self.bucket_name}.s3.{settings.AWS_REGION}.amazonaws.com/{file_key}"

    def delete_file(self, file_url: str) -> bool:
        """S3에서 파일 삭제

        Args:
            file_url: 삭제할 파일의 전체 URL

        Returns:
            삭제 성공 여부
        """
        try:
            file_key = file_url.split(f"{self.bucket_name}.s3.")[1].split("/", 1)[1]
            self.s3_client.delete_object(Bucket=self.bucket_name, Key=file_key)
            return True
        except Exception:
            return False


storage = S3Storage(
    bucket_name=settings.AWS_S3_BUCKET_NAME,
    region=settings.AWS_REGION
)
```

#### Step 3: API 엔드포인트 추가

```python
# backend/app/api/v1/users.py (기존 파일에 추가)
from fastapi import UploadFile, File, HTTPException
from app.core.storage import storage, ALLOWED_IMAGE_TYPES, MAX_FILE_SIZE


@router.post("/me/profile-image", response_model=dict)
async def upload_profile_image(
    file: UploadFile = File(...),
    current_user: User = Depends(get_current_active_user),
    db: Session = Depends(get_db)
):
    """프로필 이미지 업로드

    Args:
        file: 업로드할 이미지 파일
        current_user: 현재 로그인한 사용자
        db: 데이터베이스 세션

    Returns:
        업로드된 이미지 URL

    Raises:
        HTTPException: 파일 타입/크기 검증 실패 또는 업로드 실패
    """
    if file.content_type not in ALLOWED_IMAGE_TYPES:
        raise HTTPException(status_code=400, detail="Invalid file type")

    try:
        file_url = storage.upload_file(
            file.file,
            file.content_type,
            folder=f"profiles/{current_user.id}"
        )

        # RAW SQL: UPDATE users SET profile_image = ? WHERE id = ?
        if current_user.profile_image:
            storage.delete_file(current_user.profile_image)

        current_user.profile_image = file_url
        db.commit()

        return {"url": file_url}

    except ValueError as e:
        raise HTTPException(status_code=400, detail=str(e))
    except Exception as e:
        raise HTTPException(status_code=500, detail="Upload failed")
```

#### Step 4: User 모델에 필드 추가

```python
# backend/app/models/user.py (기존 파일 수정)
class User(Base):
    __tablename__ = "users"

    # 기존 필드들...
    profile_image = Column(String(500), nullable=True)  # 추가
```

### ⏱️ 예상 시간
- AWS S3 설정: 30분
- 구현: 1.5시간
- 테스트: 30분
- **총 2-3시간**

### 📚 참고 자료
- [Boto3 공식 문서](https://boto3.amazonaws.com/v1/documentation/api/latest/index.html)
- [FastAPI 파일 업로드](https://fastapi.tiangolo.com/tutorial/request-files/)

---

## 2. 이메일 인증

### 📌 개요
회원가입 시 이메일 인증 코드를 발송하고, 인증 완료 후 활성화합니다.

**사용 사례**: 회원가입 이메일 확인, 비밀번호 재설정, 알림 메일

### 📦 필요한 패키지

```bash
# backend/requirements.txt에 추가
aiosmtplib==3.0.2  # 비동기 SMTP
email-validator==2.2.0  # 이미 있음
jinja2==3.1.4  # 이메일 템플릿
```

### 🔧 환경 변수

```bash
# backend/.env에 추가
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your-email@gmail.com
SMTP_PASSWORD=your-app-password  # Gmail 앱 비밀번호
SMTP_FROM=noreply@yourapp.com
```

### 🧪 TDD 구현 단계

#### Step 1: 테스트 작성

```python
# backend/tests/test_email_verification.py
import pytest
from app.core.email import send_verification_email


def test_register_sends_verification_email(client, mocker):
    """회원가입 시 인증 이메일 발송"""
    # Given: 이메일 발송 함수 모킹
    mock_send = mocker.patch("app.api.v1.auth.send_verification_email")

    # When: 회원가입
    response = client.post("/api/v1/auth/register", json={
        "username": "newuser",
        "email": "newuser@example.com",
        "password": "password123"
    })

    # Then: 201 응답, 이메일 발송됨
    assert response.status_code == 201
    mock_send.assert_called_once()


def test_verify_email_success(client):
    """이메일 인증 성공"""
    # Given: 미인증 사용자 생성
    register_resp = client.post("/api/v1/auth/register", json={
        "username": "testuser",
        "email": "test@example.com",
        "password": "password123"
    })
    verification_code = "123456"  # 실제로는 DB에서 조회

    # When: 인증 코드 제출
    response = client.post("/api/v1/auth/verify-email", json={
        "email": "test@example.com",
        "code": verification_code
    })

    # Then: 200 응답, 사용자 활성화
    assert response.status_code == 200
    assert response.json()["message"] == "Email verified successfully"


def test_verify_email_invalid_code(client):
    """잘못된 인증 코드"""
    # When: 틀린 코드 제출
    response = client.post("/api/v1/auth/verify-email", json={
        "email": "test@example.com",
        "code": "000000"
    })

    # Then: 400 에러
    assert response.status_code == 400
    assert "Invalid verification code" in response.json()["detail"]
```

#### Step 2: 이메일 유틸리티 구현

```python
# backend/app/core/email.py
import aiosmtplib
from email.message import EmailMessage
from jinja2 import Template
from app.config import settings
import random
from typing import Optional


EMAIL_TEMPLATE = """
<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif;">
    <h2>이메일 인증</h2>
    <p>안녕하세요, {{ username }}님!</p>
    <p>아래 인증 코드를 입력해주세요:</p>
    <h1 style="color: #4CAF50;">{{ code }}</h1>
    <p>이 코드는 10분 후 만료됩니다.</p>
</body>
</html>
"""


def generate_verification_code() -> str:
    """6자리 숫자 인증 코드 생성

    Returns:
        6자리 숫자 문자열
    """
    return str(random.randint(100000, 999999))


async def send_verification_email(email: str, username: str, code: str) -> bool:
    """인증 이메일 발송

    Args:
        email: 수신자 이메일
        username: 사용자 이름
        code: 인증 코드

    Returns:
        발송 성공 여부

    Raises:
        aiosmtplib.SMTPException: SMTP 오류
    """
    template = Template(EMAIL_TEMPLATE)
    html_content = template.render(username=username, code=code)

    message = EmailMessage()
    message["From"] = settings.SMTP_FROM
    message["To"] = email
    message["Subject"] = "이메일 인증 코드"
    message.set_content(html_content, subtype="html")

    try:
        await aiosmtplib.send(
            message,
            hostname=settings.SMTP_HOST,
            port=settings.SMTP_PORT,
            username=settings.SMTP_USER,
            password=settings.SMTP_PASSWORD,
            start_tls=True
        )
        return True
    except Exception:
        return False
```

#### Step 3: 인증 로직 추가

```python
# backend/app/models/user.py에 필드 추가
class User(Base):
    __tablename__ = "users"

    # 기존 필드들...
    email_verified = Column(Boolean, default=False)
    verification_code = Column(String(6), nullable=True)
    verification_code_expires = Column(DateTime, nullable=True)


# backend/app/api/v1/auth.py 수정
from datetime import datetime, timedelta
from app.core.email import send_verification_email, generate_verification_code


@router.post("/register", response_model=dict, status_code=201)
async def register(user_data: UserCreate, db: Session = Depends(get_db)):
    """회원가입 (이메일 인증 필요)"""
    # 기존 중복 체크...

    hashed_password = get_password_hash(user_data.password)
    verification_code = generate_verification_code()

    # RAW SQL: INSERT INTO users (...) VALUES (...)
    db_user = User(
        username=user_data.username,
        email=user_data.email,
        hashed_password=hashed_password,
        is_active=False,  # 인증 전에는 비활성
        email_verified=False,
        verification_code=verification_code,
        verification_code_expires=datetime.utcnow() + timedelta(minutes=10)
    )
    db.add(db_user)
    db.commit()

    # 비동기 이메일 발송
    await send_verification_email(
        email=user_data.email,
        username=user_data.username,
        code=verification_code
    )

    return {"message": "Please check your email for verification code"}


@router.post("/verify-email", response_model=dict)
def verify_email(
    email: str,
    code: str,
    db: Session = Depends(get_db)
):
    """이메일 인증 코드 확인"""
    # RAW SQL: SELECT * FROM users WHERE email = ?
    user = db.query(User).filter(User.email == email).first()
    if not user:
        raise HTTPException(status_code=404, detail="User not found")

    if user.email_verified:
        raise HTTPException(status_code=400, detail="Email already verified")

    if user.verification_code != code:
        raise HTTPException(status_code=400, detail="Invalid verification code")

    if datetime.utcnow() > user.verification_code_expires:
        raise HTTPException(status_code=400, detail="Verification code expired")

    # RAW SQL: UPDATE users SET email_verified = TRUE, is_active = TRUE WHERE id = ?
    user.email_verified = True
    user.is_active = True
    user.verification_code = None
    user.verification_code_expires = None
    db.commit()

    return {"message": "Email verified successfully"}
```

### ⏱️ 예상 시간
- Gmail 앱 비밀번호 설정: 15분
- 구현: 2시간
- 테스트: 30분
- **총 2.5-3시간**

### 📚 참고 자료
- [Gmail 앱 비밀번호 만들기](https://support.google.com/accounts/answer/185833)
- [aiosmtplib 문서](https://aiosmtplib.readthedocs.io/)

---

## 3. 비밀번호 재설정

### 📌 개요
비밀번호를 잊은 사용자가 이메일로 재설정 링크를 받아 비밀번호를 변경합니다.

**사용 사례**: "비밀번호 찾기" 기능

### 🧪 TDD 구현 단계

#### Step 1: 테스트 작성

```python
# backend/tests/test_password_reset.py
def test_request_password_reset(client, test_user, mocker):
    """비밀번호 재설정 요청"""
    # Given: 이메일 발송 모킹
    mock_send = mocker.patch("app.api.v1.auth.send_password_reset_email")

    # When: 재설정 요청
    response = client.post("/api/v1/auth/forgot-password", json={
        "email": test_user.email
    })

    # Then: 200 응답
    assert response.status_code == 200
    mock_send.assert_called_once()


def test_reset_password_with_valid_token(client, db, test_user):
    """유효한 토큰으로 비밀번호 재설정 성공"""
    # Given: 재설정 토큰 생성
    reset_token = "valid-token-123"
    test_user.password_reset_token = reset_token
    test_user.password_reset_expires = datetime.utcnow() + timedelta(hours=1)
    db.commit()

    # When: 새 비밀번호로 재설정
    response = client.post("/api/v1/auth/reset-password", json={
        "token": reset_token,
        "new_password": "newpassword123"
    })

    # Then: 200 응답, 비밀번호 변경됨
    assert response.status_code == 200

    # 새 비밀번호로 로그인 가능
    login_resp = client.post("/api/v1/auth/login", json={
        "username": test_user.username,
        "password": "newpassword123"
    })
    assert login_resp.status_code == 200


def test_reset_password_with_expired_token(client):
    """만료된 토큰으로 재설정 실패"""
    response = client.post("/api/v1/auth/reset-password", json={
        "token": "expired-token",
        "new_password": "newpassword123"
    })

    assert response.status_code == 400
    assert "expired" in response.json()["detail"].lower()
```

#### Step 2: 구현

```python
# backend/app/models/user.py에 필드 추가
class User(Base):
    __tablename__ = "users"

    # 기존 필드들...
    password_reset_token = Column(String(100), nullable=True)
    password_reset_expires = Column(DateTime, nullable=True)


# backend/app/api/v1/auth.py에 엔드포인트 추가
import secrets

@router.post("/forgot-password", response_model=dict)
async def forgot_password(email: str, db: Session = Depends(get_db)):
    """비밀번호 재설정 이메일 발송"""
    # RAW SQL: SELECT * FROM users WHERE email = ?
    user = db.query(User).filter(User.email == email).first()
    if not user:
        # 보안: 이메일 존재 여부 노출 방지
        return {"message": "If the email exists, reset link has been sent"}

    reset_token = secrets.token_urlsafe(32)
    user.password_reset_token = reset_token
    user.password_reset_expires = datetime.utcnow() + timedelta(hours=1)
    db.commit()

    reset_link = f"{settings.FRONTEND_URL}/reset-password?token={reset_token}"
    await send_password_reset_email(user.email, user.username, reset_link)

    return {"message": "If the email exists, reset link has been sent"}


@router.post("/reset-password", response_model=dict)
def reset_password(
    token: str,
    new_password: str,
    db: Session = Depends(get_db)
):
    """비밀번호 재설정 실행"""
    # RAW SQL: SELECT * FROM users WHERE password_reset_token = ?
    user = db.query(User).filter(User.password_reset_token == token).first()
    if not user:
        raise HTTPException(status_code=400, detail="Invalid reset token")

    if datetime.utcnow() > user.password_reset_expires:
        raise HTTPException(status_code=400, detail="Reset token expired")

    user.hashed_password = get_password_hash(new_password)
    user.password_reset_token = None
    user.password_reset_expires = None
    db.commit()

    return {"message": "Password reset successfully"}
```

### ⏱️ 예상 시간: **1-1.5시간**

---

## 4. 소셜 로그인 (OAuth 2.0)

### 📌 개요
Google, Kakao 등 소셜 계정으로 간편 로그인합니다.

**장점**: 가입률 30-50% 증가, 사용자 편의성

### 📦 필요한 패키지

```bash
# backend/requirements.txt에 추가
authlib==1.3.2  # OAuth 2.0 클라이언트
httpx==0.28.1  # 이미 있음 (비동기 HTTP)
```

### 🔧 환경 변수

```bash
# backend/.env에 추가
GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/api/v1/auth/google/callback

KAKAO_CLIENT_ID=your-kakao-client-id
KAKAO_CLIENT_SECRET=your-kakao-client-secret
KAKAO_REDIRECT_URI=http://localhost:8000/api/v1/auth/kakao/callback
```

### 🧪 TDD 구현 단계

#### Step 1: 테스트 작성

```python
# backend/tests/test_social_login.py
def test_google_login_redirect(client):
    """Google 로그인 리다이렉트"""
    response = client.get("/api/v1/auth/google/login")

    assert response.status_code == 302  # Redirect
    assert "accounts.google.com" in response.headers["location"]


def test_google_callback_creates_user(client, db, mocker):
    """Google 콜백으로 신규 사용자 생성"""
    # Given: Google API 응답 모킹
    mock_userinfo = {
        "email": "googleuser@gmail.com",
        "name": "Google User",
        "picture": "https://example.com/avatar.jpg"
    }
    mocker.patch("app.api.v1.auth.get_google_userinfo", return_value=mock_userinfo)

    # When: 콜백 처리
    response = client.get("/api/v1/auth/google/callback?code=valid-auth-code")

    # Then: 사용자 생성, JWT 토큰 발급
    assert response.status_code == 200
    data = response.json()
    assert "access_token" in data

    # RAW SQL: SELECT * FROM users WHERE email = ?
    user = db.query(User).filter(User.email == "googleuser@gmail.com").first()
    assert user is not None
    assert user.oauth_provider == "google"
```

#### Step 2: 구현

```python
# backend/app/models/user.py에 필드 추가
class User(Base):
    __tablename__ = "users"

    # 기존 필드들...
    oauth_provider = Column(String(20), nullable=True)  # "google", "kakao"
    oauth_id = Column(String(100), nullable=True)


# backend/app/api/v1/auth.py에 엔드포인트 추가
from authlib.integrations.starlette_client import OAuth

oauth = OAuth()
oauth.register(
    name='google',
    client_id=settings.GOOGLE_CLIENT_ID,
    client_secret=settings.GOOGLE_CLIENT_SECRET,
    server_metadata_url='https://accounts.google.com/.well-known/openid-configuration',
    client_kwargs={'scope': 'openid email profile'}
)


@router.get("/google/login")
async def google_login(request: Request):
    """Google 로그인 리다이렉트"""
    redirect_uri = settings.GOOGLE_REDIRECT_URI
    return await oauth.google.authorize_redirect(request, redirect_uri)


@router.get("/google/callback")
async def google_callback(request: Request, db: Session = Depends(get_db)):
    """Google 콜백 처리"""
    token = await oauth.google.authorize_access_token(request)
    userinfo = token.get('userinfo')

    # RAW SQL: SELECT * FROM users WHERE email = ? AND oauth_provider = 'google'
    user = db.query(User).filter(
        User.email == userinfo['email'],
        User.oauth_provider == 'google'
    ).first()

    if not user:
        # 신규 사용자 생성
        user = User(
            username=userinfo['email'].split('@')[0],
            email=userinfo['email'],
            oauth_provider='google',
            oauth_id=userinfo['sub'],
            is_active=True,
            email_verified=True
        )
        db.add(user)
        db.commit()
        db.refresh(user)

    access_token = create_access_token(data={"sub": str(user.id)})
    refresh_token = create_refresh_token(data={"sub": str(user.id)})

    return {
        "access_token": access_token,
        "refresh_token": refresh_token,
        "token_type": "bearer"
    }
```

### ⏱️ 예상 시간
- OAuth 앱 등록: 30분 (Google Console, Kakao Developers)
- 구현: 2시간
- **총 2.5-3시간**

### 📚 참고 자료
- [Google OAuth 2.0 설정](https://console.cloud.google.com/apis/credentials)
- [Kakao Developers](https://developers.kakao.com/)
- [Authlib 문서](https://docs.authlib.org/)

---

## 5. 페이지네이션

### 📌 개요
대량의 데이터를 효율적으로 조회하기 위한 페이지네이션 구현입니다.

**방식**:
- **Offset 기반**: 간단, 페이지 번호 사용
- **Cursor 기반**: 성능 좋음, 무한 스크롤

### 🧪 TDD 구현 단계

#### Step 1: 테스트 작성

```python
# backend/tests/test_pagination.py
def test_get_users_with_pagination(client, test_users):
    """페이지네이션으로 사용자 목록 조회"""
    # Given: 100명의 사용자 생성

    # When: 첫 페이지 조회 (20개씩)
    response = client.get("/api/v1/users?page=1&size=20")

    # Then: 200 응답, 페이지 정보 포함
    assert response.status_code == 200
    data = response.json()
    assert len(data["items"]) == 20
    assert data["total"] == 100
    assert data["page"] == 1
    assert data["size"] == 20
    assert data["pages"] == 5


def test_get_users_cursor_based(client, test_users):
    """Cursor 기반 페이지네이션"""
    # When: 처음 20개 조회
    response = client.get("/api/v1/users?limit=20")
    data = response.json()

    # Then: next_cursor 반환
    assert len(data["items"]) == 20
    assert "next_cursor" in data

    # When: next_cursor로 다음 페이지 조회
    next_response = client.get(f"/api/v1/users?limit=20&cursor={data['next_cursor']}")
    next_data = next_response.json()

    # Then: 다음 20개 반환
    assert len(next_data["items"]) == 20
    assert next_data["items"][0]["id"] != data["items"][0]["id"]
```

#### Step 2: 구현

```python
# backend/app/schemas/pagination.py (새 파일)
from pydantic import BaseModel
from typing import List, Generic, TypeVar, Optional

T = TypeVar('T')


class PaginatedResponse(BaseModel, Generic[T]):
    """Offset 기반 페이지네이션 응답

    Attributes:
        items: 현재 페이지 아이템 목록
        total: 전체 아이템 개수
        page: 현재 페이지 번호 (1부터 시작)
        size: 페이지당 아이템 개수
        pages: 전체 페이지 수
    """
    items: List[T]
    total: int
    page: int
    size: int
    pages: int


class CursorPaginatedResponse(BaseModel, Generic[T]):
    """Cursor 기반 페이지네이션 응답

    Attributes:
        items: 현재 페이지 아이템 목록
        next_cursor: 다음 페이지 커서 (없으면 null)
        has_more: 다음 페이지 존재 여부
    """
    items: List[T]
    next_cursor: Optional[str]
    has_more: bool


# backend/app/api/v1/users.py에 엔드포인트 추가
from app.schemas.pagination import PaginatedResponse, CursorPaginatedResponse
from sqlalchemy import func
import base64


@router.get("/", response_model=PaginatedResponse[UserResponse])
def get_users(
    page: int = 1,
    size: int = 20,
    db: Session = Depends(get_db)
):
    """사용자 목록 조회 (Offset 페이지네이션)

    Args:
        page: 페이지 번호 (1부터 시작)
        size: 페이지당 아이템 개수
        db: 데이터베이스 세션

    Returns:
        페이지네이션된 사용자 목록
    """
    if page < 1:
        page = 1
    if size < 1 or size > 100:
        size = 20

    offset = (page - 1) * size

    # RAW SQL: SELECT COUNT(*) FROM users
    total = db.query(func.count(User.id)).scalar()

    # RAW SQL: SELECT * FROM users LIMIT ? OFFSET ?
    users = db.query(User).offset(offset).limit(size).all()

    pages = (total + size - 1) // size  # 올림 나누기

    return PaginatedResponse(
        items=users,
        total=total,
        page=page,
        size=size,
        pages=pages
    )


@router.get("/cursor", response_model=CursorPaginatedResponse[UserResponse])
def get_users_cursor(
    limit: int = 20,
    cursor: Optional[str] = None,
    db: Session = Depends(get_db)
):
    """사용자 목록 조회 (Cursor 페이지네이션)

    Args:
        limit: 조회할 아이템 개수
        cursor: 이전 페이지의 마지막 ID (base64 인코딩)
        db: 데이터베이스 세션

    Returns:
        Cursor 기반 페이지네이션된 사용자 목록
    """
    if limit < 1 or limit > 100:
        limit = 20

    query = db.query(User)

    if cursor:
        # Cursor 디코딩
        cursor_id = int(base64.b64decode(cursor).decode())
        # RAW SQL: SELECT * FROM users WHERE id > ? ORDER BY id LIMIT ?
        query = query.filter(User.id > cursor_id)

    query = query.order_by(User.id).limit(limit + 1)
    users = query.all()

    has_more = len(users) > limit
    if has_more:
        users = users[:limit]

    next_cursor = None
    if has_more and users:
        next_cursor = base64.b64encode(str(users[-1].id).encode()).decode()

    return CursorPaginatedResponse(
        items=users,
        next_cursor=next_cursor,
        has_more=has_more
    )
```

### ⏱️ 예상 시간: **1-1.5시간**

---

## 6. 검색/필터링

### 📌 개요
키워드 검색 및 다중 조건 필터링 구현입니다.

### 🧪 TDD 구현 단계

```python
# backend/tests/test_search.py
def test_search_users_by_keyword(client, test_users):
    """키워드로 사용자 검색"""
    # When: "john"으로 검색
    response = client.get("/api/v1/users/search?q=john")

    # Then: username 또는 email에 "john" 포함된 사용자만 반환
    assert response.status_code == 200
    data = response.json()
    for user in data["items"]:
        assert "john" in user["username"].lower() or "john" in user["email"].lower()


def test_filter_users_by_status(client, test_users):
    """상태로 필터링"""
    # When: 활성 사용자만 조회
    response = client.get("/api/v1/users?is_active=true")

    # Then: is_active=True인 사용자만 반환
    data = response.json()
    for user in data["items"]:
        assert user["is_active"] is True


# backend/app/api/v1/users.py에 검색 엔드포인트 추가
@router.get("/search", response_model=PaginatedResponse[UserResponse])
def search_users(
    q: str,
    is_active: Optional[bool] = None,
    page: int = 1,
    size: int = 20,
    db: Session = Depends(get_db)
):
    """사용자 검색 및 필터링

    Args:
        q: 검색 키워드 (username, email)
        is_active: 활성 상태 필터 (선택)
        page: 페이지 번호
        size: 페이지당 개수
        db: 데이터베이스 세션

    Returns:
        검색/필터링된 사용자 목록
    """
    # RAW SQL: SELECT * FROM users WHERE (username LIKE ? OR email LIKE ?)
    query = db.query(User).filter(
        (User.username.contains(q)) | (User.email.contains(q))
    )

    if is_active is not None:
        query = query.filter(User.is_active == is_active)

    total = query.count()
    offset = (page - 1) * size
    users = query.offset(offset).limit(size).all()

    return PaginatedResponse(
        items=users,
        total=total,
        page=page,
        size=size,
        pages=(total + size - 1) // size
    )
```

### ⏱️ 예상 시간: **1시간**

---

## 7. 푸시 알림 (FCM)

### 📌 개요
Firebase Cloud Messaging을 사용한 모바일 푸시 알림입니다.

### 📦 필요한 패키지

```bash
# backend/requirements.txt에 추가
firebase-admin==6.5.0

# frontend/MobileApp/package.json에 추가
npm install @react-native-firebase/app @react-native-firebase/messaging
```

### 🔧 환경 변수

```bash
# backend/.env에 추가
FIREBASE_CREDENTIALS_PATH=/app/firebase-credentials.json
```

### 🧪 TDD 구현 단계

```python
# backend/tests/test_push_notification.py
def test_save_device_token(client, test_user_token):
    """디바이스 토큰 저장"""
    response = client.post(
        "/api/v1/users/me/device-token",
        headers={"Authorization": f"Bearer {test_user_token}"},
        json={"token": "fcm-device-token-123"}
    )

    assert response.status_code == 200


def test_send_push_notification(client, mocker):
    """푸시 알림 발송"""
    mock_send = mocker.patch("firebase_admin.messaging.send")

    # When: 알림 발송
    from app.core.push import send_push_notification
    send_push_notification(
        token="fcm-device-token-123",
        title="새 메시지",
        body="안녕하세요!"
    )

    # Then: Firebase API 호출됨
    mock_send.assert_called_once()


# backend/app/core/push.py (새 파일)
import firebase_admin
from firebase_admin import credentials, messaging
from app.config import settings


cred = credentials.Certificate(settings.FIREBASE_CREDENTIALS_PATH)
firebase_admin.initialize_app(cred)


def send_push_notification(token: str, title: str, body: str) -> bool:
    """푸시 알림 발송

    Args:
        token: FCM 디바이스 토큰
        title: 알림 제목
        body: 알림 내용

    Returns:
        발송 성공 여부
    """
    message = messaging.Message(
        notification=messaging.Notification(title=title, body=body),
        token=token
    )

    try:
        messaging.send(message)
        return True
    except Exception:
        return False
```

### ⏱️ 예상 시간: **2-3시간**

### 📚 참고 자료
- [Firebase Console](https://console.firebase.google.com/)
- [React Native Firebase](https://rnfirebase.io/)

---

## 8. 에러 트래킹 (Sentry)

### 📌 개요
프로덕션 환경의 에러를 실시간으로 추적하고 알림받습니다.

### 📦 필요한 패키지

```bash
# backend/requirements.txt에 추가
sentry-sdk[fastapi]==2.19.2

# frontend/MobileApp/package.json에 추가
npm install @sentry/react-native
```

### 🔧 환경 변수

```bash
# backend/.env에 추가
SENTRY_DSN=https://xxx@xxx.ingest.sentry.io/xxx
ENVIRONMENT=production  # 또는 development
```

### 구현

```python
# backend/app/main.py 수정
import sentry_sdk
from sentry_sdk.integrations.fastapi import FastApiIntegration
from app.config import settings

if settings.SENTRY_DSN:
    sentry_sdk.init(
        dsn=settings.SENTRY_DSN,
        environment=settings.ENVIRONMENT,
        integrations=[FastApiIntegration()],
        traces_sample_rate=0.1,  # 10% 트랜잭션 샘플링
    )

app = FastAPI(title=settings.PROJECT_NAME)

# 에러 발생 시 자동으로 Sentry에 전송됨
```

```typescript
// frontend/MobileApp/index.js 수정
import * as Sentry from '@sentry/react-native';

Sentry.init({
  dsn: 'https://xxx@xxx.ingest.sentry.io/xxx',
  environment: 'production',
});

AppRegistry.registerComponent(appName, () => App);
```

### ⏱️ 예상 시간: **1시간**

### 📚 참고 자료
- [Sentry 공식 사이트](https://sentry.io/)

---

## 9. Admin 대시보드

### 📌 개요
관리자가 사용자, 데이터를 관리할 수 있는 대시보드입니다.

### 📦 필요한 패키지

```bash
# backend/requirements.txt에 추가
sqladmin==0.19.0
```

### 구현

```python
# backend/app/admin.py (새 파일)
from sqladmin import Admin, ModelView
from app.models.user import User
from app.database import engine


class UserAdmin(ModelView, model=User):
    """사용자 관리 Admin 뷰"""
    column_list = [User.id, User.username, User.email, User.is_active, User.created_at]
    column_searchable_list = [User.username, User.email]
    column_sortable_list = [User.id, User.created_at]
    can_create = False
    can_delete = False
    can_edit = True


# backend/app/main.py에 추가
from app.admin import UserAdmin
from sqladmin import Admin

admin = Admin(app, engine)
admin.add_view(UserAdmin)

# http://localhost:8000/admin 접속
```

### ⏱️ 예상 시간: **1.5-2시간**

---

## 10. Redis 캐싱

### 📌 개요
자주 조회되는 데이터를 캐싱하여 성능을 향상시킵니다.

### 📦 필요한 패키지

```bash
# backend/requirements.txt에 추가
redis==5.2.1
aioredis==2.0.1
```

### 🔧 Docker Compose 수정

```yaml
# docker-compose.dev.yml에 추가
services:
  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data

volumes:
  redis_data:
```

### 구현

```python
# backend/app/core/cache.py (새 파일)
import redis
from app.config import settings
import json
from typing import Optional, Any


redis_client = redis.Redis(
    host=settings.REDIS_HOST,
    port=settings.REDIS_PORT,
    db=0,
    decode_responses=True
)


def cache_get(key: str) -> Optional[Any]:
    """캐시 조회

    Args:
        key: 캐시 키

    Returns:
        캐시된 값 (없으면 None)
    """
    value = redis_client.get(key)
    return json.loads(value) if value else None


def cache_set(key: str, value: Any, expire: int = 3600) -> bool:
    """캐시 저장

    Args:
        key: 캐시 키
        value: 저장할 값
        expire: 만료 시간 (초)

    Returns:
        저장 성공 여부
    """
    return redis_client.setex(key, expire, json.dumps(value))


# backend/app/api/v1/users.py 수정
from app.core.cache import cache_get, cache_set

@router.get("/{user_id}", response_model=UserResponse)
def get_user(user_id: int, db: Session = Depends(get_db)):
    """사용자 조회 (캐싱 적용)"""
    cache_key = f"user:{user_id}"

    # 캐시 확인
    cached = cache_get(cache_key)
    if cached:
        return cached

    # RAW SQL: SELECT * FROM users WHERE id = ?
    user = db.query(User).filter(User.id == user_id).first()
    if not user:
        raise HTTPException(status_code=404, detail="User not found")

    # 캐시 저장 (1시간)
    cache_set(cache_key, user.dict(), expire=3600)

    return user
```

### ⏱️ 예상 시간: **1.5-2시간**

---

## 11. Celery 배치 작업

### 📌 개요
시간이 오래 걸리는 작업을 비동기로 처리합니다.

**사용 사례**: 대량 이메일 발송, 리포트 생성, 데이터 정리

### 📦 필요한 패키지

```bash
# backend/requirements.txt에 추가
celery==5.4.0
celery[redis]==5.4.0
```

### 구현

```python
# backend/app/celery_app.py (새 파일)
from celery import Celery
from app.config import settings

celery_app = Celery(
    "worker",
    broker=f"redis://{settings.REDIS_HOST}:{settings.REDIS_PORT}/0",
    backend=f"redis://{settings.REDIS_HOST}:{settings.REDIS_PORT}/0"
)


@celery_app.task
def send_bulk_emails(user_ids: list[int]):
    """대량 이메일 발송 작업

    Args:
        user_ids: 이메일 발송 대상 사용자 ID 목록
    """
    from app.database import SessionLocal
    from app.core.email import send_email

    db = SessionLocal()
    users = db.query(User).filter(User.id.in_(user_ids)).all()

    for user in users:
        send_email(user.email, "제목", "내용")

    db.close()


# backend/app/api/v1/admin.py (새 파일)
from app.celery_app import send_bulk_emails

@router.post("/send-newsletter")
def send_newsletter(current_user: User = Depends(get_current_admin_user)):
    """뉴스레터 발송 (비동기)"""
    from app.database import get_db
    db = next(get_db())

    user_ids = [u.id for u in db.query(User).filter(User.is_active == True).all()]

    # 비동기 작업 큐에 추가
    send_bulk_emails.delay(user_ids)

    return {"message": f"Newsletter queued for {len(user_ids)} users"}
```

### 실행

```bash
# Celery Worker 시작
docker-compose -f docker-compose.dev.yml exec backend celery -A app.celery_app worker --loglevel=info
```

### ⏱️ 예상 시간: **2-3시간**

---

## 📊 우선순위 요약

| 순위 | 기능 | 예상 시간 | 재사용성 | 난이도 |
|------|------|-----------|----------|--------|
| 1 | 파일 업로드 (S3) | 2-3시간 | ⭐⭐⭐⭐⭐ | 중 |
| 2 | 이메일 인증 | 2.5-3시간 | ⭐⭐⭐⭐⭐ | 중 |
| 3 | 비밀번호 재설정 | 1-1.5시간 | ⭐⭐⭐⭐⭐ | 하 |
| 4 | 페이지네이션 | 1-1.5시간 | ⭐⭐⭐⭐⭐ | 하 |
| 5 | 소셜 로그인 | 2.5-3시간 | ⭐⭐⭐⭐ | 중 |
| 6 | 검색/필터링 | 1시간 | ⭐⭐⭐⭐ | 하 |
| 7 | Sentry | 1시간 | ⭐⭐⭐⭐⭐ | 하 |
| 8 | 푸시 알림 | 2-3시간 | ⭐⭐⭐⭐ | 중 |
| 9 | Redis 캐싱 | 1.5-2시간 | ⭐⭐⭐⭐ | 중 |
| 10 | Admin 대시보드 | 1.5-2시간 | ⭐⭐⭐⭐ | 하 |
| 11 | Celery | 2-3시간 | ⭐⭐⭐ | 상 |

---

## 🎯 추천 로드맵

### Phase 1 (v0.10.0) - 필수 기능
1. ✅ 파일 업로드 (S3/MinIO)
2. ✅ 이메일 인증
3. ✅ 비밀번호 재설정
4. ✅ 페이지네이션

**예상 시간**: 7-9시간

### Phase 2 (v0.11.0) - 사용자 경험
5. ✅ 소셜 로그인 (Google, Kakao)
6. ✅ 검색/필터링
7. ✅ Sentry

**예상 시간**: 4.5-6시간

### Phase 3 (v1.0.0) - 프로덕션 준비
8. ✅ 푸시 알림
9. ✅ Redis 캐싱
10. ✅ Admin 대시보드
11. ✅ Celery

**예상 시간**: 7-10시간

---

## 📝 참고 사항

### 모든 확장 작업 시 필수 체크리스트

- [ ] TDD 방식으로 테스트 먼저 작성
- [ ] 타입 힌트 모든 함수에 적용
- [ ] Docstring 작성 (Args, Returns, Raises)
- [ ] 환경 변수로 민감 정보 관리
- [ ] 에러 메시지에 민감 정보 노출 금지
- [ ] 커버리지 80% 이상 유지
- [ ] Docker 환경에서 개발
- [ ] 보안 검증 (`bash scripts/check-secrets.sh`)
- [ ] 문서 업데이트 (`docs/`)
- [ ] Conventional Commit 규칙 준수

### 도움이 필요할 때

1. 각 기능의 공식 문서 참조
2. GitHub Issues 검색
3. Claude와 협업 시: "TDD로 {기능명} 추가해줘. EXTENSIONS.md 5번 참고."

---

**마지막 업데이트**: 2025-10-18
**템플릿 버전**: v0.9.5
