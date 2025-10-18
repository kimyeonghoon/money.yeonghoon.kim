# 보안 가이드

이 문서는 프로젝트의 민감 정보를 안전하게 관리하는 방법을 설명합니다.

## 📋 목차

1. [민감 정보란?](#민감-정보란)
2. [예방 조치](#예방-조치)
3. [민감 정보 관리](#민감-정보-관리)
4. [이미 커밋된 경우](#이미-커밋된-경우)
5. [정기 검사](#정기-검사)
6. [보안 체크리스트](#보안-체크리스트)

---

## 민감 정보란?

### 🚫 절대 git에 커밋하면 안 되는 것

1. **환경 변수 파일**
   - `.env`, `.env.local`, `.env.production`
   - 데이터베이스 비밀번호
   - API 키, SECRET_KEY

2. **인증 정보**
   - AWS Access Key / Secret Key
   - API 토큰
   - JWT Secret
   - OAuth Client Secret

3. **인증서 및 키**
   - Private Key (*.pem, *.key)
   - SSL 인증서
   - SSH 키

4. **데이터베이스**
   - 데이터베이스 덤프 (*.sql)
   - 백업 파일
   - 실제 사용자 데이터

---

## 예방 조치

### 1. `.gitignore` 확인

프로젝트에 이미 강화된 `.gitignore`가 있습니다:

```bash
# .gitignore 내용 확인
cat .gitignore
```

**주요 패턴:**
```gitignore
# 환경 변수
.env
.env.*
*.env
!.env.example

# 민감 정보
secrets/
credentials/
*.credentials

# 키 파일
*.pem
*.key
*.keystore
```

### 2. Pre-commit Hook 설치

자동으로 민감 정보를 검사합니다:

```bash
# Git hooks 경로 설정
git config core.hooksPath .githooks

# Hook 실행 권한 부여 (Linux/macOS)
chmod +x .githooks/pre-commit

# Windows Git Bash
git update-index --chmod=+x .githooks/pre-commit
```

**작동 방식:**
```bash
git add .
git commit -m "commit message"
# → 자동으로 민감 정보 검사
# → 발견 시 커밋 차단
```

### 3. 수동 검사

커밋 전에 수동으로 검사:

```bash
bash scripts/check-secrets.sh
```

---

## 민감 정보 관리

### 1. 환경 변수 사용

#### ❌ 나쁜 예 (하드코딩)

```python
# backend/app/config.py
SECRET_KEY = "my-super-secret-key-12345"  # ❌
DATABASE_URL = "mysql://root:password123@localhost/db"  # ❌
```

#### ✅ 좋은 예 (환경 변수)

```python
# backend/app/config.py
import os
from pydantic_settings import BaseSettings

class Settings(BaseSettings):
    SECRET_KEY: str
    DATABASE_URL: str

    class Config:
        env_file = ".env"

settings = Settings()
```

**`.env` 파일:**
```env
SECRET_KEY=my-super-secret-key-12345
DATABASE_URL=mysql://root:password123@localhost/db
```

**`.env.example` 파일 (커밋 가능):**
```env
SECRET_KEY=your-secret-key-here
DATABASE_URL=mysql://user:password@localhost/dbname
```

### 2. Docker Secrets (프로덕션)

```yaml
# docker-compose.yml
version: '3.8'

services:
  backend:
    environment:
      - SECRET_KEY_FILE=/run/secrets/secret_key
    secrets:
      - secret_key

secrets:
  secret_key:
    external: true
```

### 3. AWS Secrets Manager / GCP Secret Manager

프로덕션 환경에서는 클라우드 secrets 관리 서비스 사용:

```python
# 예시: AWS Secrets Manager
import boto3
import json

def get_secret(secret_name):
    client = boto3.client('secretsmanager')
    response = client.get_secret_value(SecretId=secret_name)
    return json.loads(response['SecretString'])
```

---

## 이미 커밋된 경우

### 🚨 긴급 대응

민감 정보를 실수로 커밋했다면:

#### 1. **즉시 비밀번호/키 변경**

```bash
# 데이터베이스 비밀번호 변경
# API 키 재생성
# AWS 키 비활성화 및 재생성
```

#### 2. **아직 푸시 안 한 경우**

```bash
# 마지막 커밋 취소
git reset HEAD~1

# 또는 특정 파일만 제거
git rm --cached .env
git commit --amend
```

#### 3. **이미 푸시한 경우**

**방법 A: BFG Repo-Cleaner (추천)**

```bash
# BFG 다운로드
# https://rtyley.github.io/bfg-repo-cleaner/

# 민감 정보 제거
java -jar bfg.jar --delete-files .env

# Git history 정리
git reflog expire --expire=now --all
git gc --prune=now --aggressive

# Force push
git push --force
```

**방법 B: git filter-branch**

```bash
# .env 파일을 히스토리에서 완전 제거
git filter-branch --force --index-filter \
  "git rm --cached --ignore-unmatch backend/.env" \
  --prune-empty --tag-name-filter cat -- --all

# Force push
git push --force --all
```

#### 4. **팀원에게 알림**

```
⚠️  긴급: Git History 재작성

민감 정보 제거를 위해 git history를 재작성했습니다.

모든 팀원은 다음 작업 필요:
1. 로컬 작업 백업
2. git fetch origin
3. git reset --hard origin/main
4. 작업 브랜치 재생성
```

---

## 정기 검사

### 1. 로컬 검사 (개발자)

```bash
# 커밋 전
bash scripts/check-secrets.sh

# 또는 git commit 시 자동 검사 (pre-commit hook)
git commit -m "message"
```

### 2. CI/CD 검사

**GitHub Actions:**

```yaml
# .github/workflows/security.yml
name: Security Check

on: [push, pull_request]

jobs:
  secrets-scan:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3

      - name: Run secrets check
        run: bash scripts/check-secrets.sh

      - name: TruffleHog Scan
        uses: trufflesecurity/trufflehog@main
        with:
          path: ./
          base: main
          head: HEAD
```

### 3. 서드파티 도구

**추천 도구:**

1. **TruffleHog**
   ```bash
   # Docker로 실행
   docker run --rm -v $(pwd):/repo trufflesecurity/trufflehog:latest filesystem /repo
   ```

2. **git-secrets** (AWS)
   ```bash
   # 설치
   brew install git-secrets  # macOS
   apt-get install git-secrets  # Ubuntu

   # 프로젝트에 추가
   git secrets --install
   git secrets --register-aws
   ```

3. **Gitleaks**
   ```bash
   # 설치
   brew install gitleaks  # macOS

   # 검사
   gitleaks detect --source . --verbose
   ```

---

## 보안 체크리스트

### 🔒 개발 시작 전

- [ ] `.gitignore` 설정 확인
- [ ] Pre-commit hook 설치
- [ ] `.env.example` 파일 확인
- [ ] `.env` 파일을 `.env.example`에서 복사하여 생성

### 🔒 코드 작성 시

- [ ] 비밀번호/키를 하드코딩하지 않음
- [ ] 환경 변수 사용 (`os.getenv()`, `process.env`)
- [ ] `.env` 파일을 git에 추가하지 않음
- [ ] 로그에 민감 정보 출력하지 않음

### 🔒 커밋 전

- [ ] `bash scripts/check-secrets.sh` 실행
- [ ] 커밋할 파일 목록 확인 (`git status`)
- [ ] Diff 확인 (`git diff --cached`)
- [ ] `.env` 파일이 포함되지 않았는지 재확인

### 🔒 푸시 전

- [ ] 마지막 커밋 다시 확인
- [ ] 테스트 통과 확인
- [ ] 민감 정보 없는지 최종 확인

### 🔒 주기적으로

- [ ] 전체 프로젝트 검사 (`bash scripts/check-secrets.sh`)
- [ ] Git history 검사 (TruffleHog, Gitleaks)
- [ ] 의존성 보안 업데이트 확인
- [ ] 사용하지 않는 API 키 삭제

---

## 📞 보안 이슈 보고

민감 정보가 노출된 것을 발견했다면:

1. **즉시 알림**: 팀 리더에게 보고
2. **비공개 보고**: 이슈 트래커에 공개하지 말 것
3. **긴급 대응**: 키/비밀번호 즉시 변경
4. **Git history 정리**: 위 가이드 참조

---

## 🔗 참고 자료

### 도구

- [BFG Repo-Cleaner](https://rtyley.github.io/bfg-repo-cleaner/)
- [TruffleHog](https://github.com/trufflesecurity/trufflehog)
- [Gitleaks](https://github.com/gitleaks/gitleaks)
- [git-secrets](https://github.com/awslabs/git-secrets)

### 가이드

- [GitHub: Removing sensitive data](https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/removing-sensitive-data-from-a-repository)
- [OWASP: Secrets Management](https://cheatsheetseries.owasp.org/cheatsheets/Secrets_Management_Cheat_Sheet.html)

---

**마지막 업데이트**: 2025-01-17

**모든 개발자는 이 가이드를 숙지하고 준수해야 합니다.**
