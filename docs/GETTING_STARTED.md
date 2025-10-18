# 시작하기: 템플릿으로 새 프로젝트 만들기

이 가이드는 템플릿을 사용해서 실제 프로젝트를 시작하는 방법을 단계별로 설명합니다.

## 📋 목차

1. [템플릿 복사 및 초기화](#1-템플릿-복사-및-초기화)
2. [프로젝트 커스터마이징](#2-프로젝트-커스터마이징)
3. [개발 환경 시작](#3-개발-환경-시작)
4. [첫 기능 개발](#4-첫-기능-개발)
5. [개발 워크플로우](#5-개발-워크플로우)

---

## 1. 템플릿 복사 및 초기화

### 1.1 템플릿 복사

```bash
# 템플릿을 새 프로젝트로 복사
cp -r fullstack_template my-awesome-app
cd my-awesome-app

# 또는 Git으로 관리하는 경우
git clone <template-repo-url> my-awesome-app
cd my-awesome-app
rm -rf .git  # 템플릿의 git 히스토리 제거
```

### 1.2 Git 초기화

```bash
# 새로운 Git 저장소 시작
git init
git add .
git commit -m "chore: 프로젝트 초기화 (fullstack template 기반)"

# GitHub/GitLab 등에 연결
git remote add origin <your-repo-url>
git push -u origin main
```

### 1.3 환경 변수 설정

```bash
# 백엔드 환경 변수 생성
cp backend/.env.example backend/.env

# .env 파일 편집
# - SECRET_KEY: 새로 생성 (openssl rand -hex 32)
# - 데이터베이스 비밀번호 변경
# - 프로젝트명 변경
```

---

## 2. 프로젝트 커스터마이징

### 2.1 프로젝트 정보 변경

#### `backend/app/config.py`
```python
class Settings(BaseSettings):
    # 프로젝트 기본 설정
    PROJECT_NAME: str = "My Awesome App"  # ← 변경
    VERSION: str = "0.1.0"                # ← 초기 버전
    API_V1_STR: str = "/api/v1"
```

#### `backend/.env`
```env
PROJECT_NAME=My Awesome App
SECRET_KEY=<새로-생성한-시크릿-키>  # openssl rand -hex 32

# 데이터베이스 설정 (프로덕션용은 강력한 비밀번호)
MYSQL_PASSWORD=<강력한-비밀번호>
MYSQL_DATABASE=myapp_db  # 프로젝트명에 맞게
```

#### `docker-compose.yml` 및 `docker-compose.dev.yml`
```yaml
services:
  mysql:
    container_name: myapp_mysql_dev  # ← 변경
    environment:
      MYSQL_DATABASE: myapp_db       # ← 변경

  backend:
    container_name: myapp_backend_dev  # ← 변경
```

#### `README.md`
```markdown
# My Awesome App

프로젝트 설명을 여기에 작성...
```

### 2.2 불필요한 파일 정리

```bash
# 템플릿 설명 파일들 제거 또는 수정
rm GETTING_STARTED.md  # 이 파일 (읽은 후)
rm VERSIONS.md         # 또는 프로젝트 버전으로 새로 작성
rm DEVELOPMENT.md      # 또는 프로젝트에 맞게 수정

# 프론트엔드 템플릿 파일은 유지
# (React Native 프로젝트 생성 후 사용)
```

---

## 3. 개발 환경 시작

### 3.0 빠른 시작 (헬퍼 스크립트 사용)

모든 환경을 한 번에 시작:

```bash
# 개발 환경 전체 시작
bash scripts/dev-start.sh

# 또는 Android 기기 IP와 함께
bash scripts/dev-start.sh 192.168.0.100

# Windows PowerShell
.\scripts\dev-start.ps1 192.168.0.100
```

이 스크립트가 자동으로:
- ✅ 백엔드 API 서버 시작
- ✅ Metro 서버 시작
- ✅ WiFi adb 연결
- ✅ 앱 빌드 및 설치

**자세한 내용:** [scripts/README.md](../scripts/README.md)

---

### 3.1 백엔드 개발 환경 시작 (수동)

**방법 A: VS Code Dev Container (권장)**

```bash
# 1. VS Code로 프로젝트 열기
code .

# 2. F1 → "Dev Containers: Reopen in Container"

# 3. 완료! 터미널이 컨테이너 안에서 실행됨
```

**방법 B: Docker Compose**

```bash
# 개발 모드로 시작
docker-compose -f docker-compose.dev.yml up -d

# 로그 확인
docker-compose -f docker-compose.dev.yml logs -f backend

# API 테스트
curl http://localhost:8000
curl http://localhost:8000/docs
```

### 3.2 프론트엔드 초기화 (선택)

```bash
cd frontend
bash docker-setup.sh

# 프로젝트 이름 입력 (예: MyAwesomeApp)
# Docker 컨테이너에서 자동으로 프로젝트 생성 및 패키지 설치

# 완료 후 Metro 서버 시작:
cd ..
docker-compose -f docker-compose.dev.yml up -d frontend
```

### 3.3 WiFi 디버깅 설정 (완전 Docker 개발)

```bash
# 1. Android 기기를 WiFi 디버깅 모드로 설정
# 설정 → 개발자 옵션 → 무선 디버깅 활성화

# 2. Docker에서 adb 연결
docker-compose -f docker-compose.dev.yml exec frontend adb connect 192.168.0.100:5555

# 3. 앱 빌드 및 설치
docker-compose -f docker-compose.dev.yml exec frontend npm run android

# Node.js 로컬 설치 없이 완전히 Docker로만 개발!
```

**자세한 가이드:** [WIFI_DEBUGGING.md](WIFI_DEBUGGING.md)

---

## 4. 첫 기능 개발

### 4.1 시작 전 체크리스트

- [ ] 개발 환경 정상 작동 확인 (`http://localhost:8000/docs`)
- [ ] 데이터베이스 연결 확인
- [ ] Git 설정 완료
- [ ] 팀원과 개발 규칙 합의 (`CLAUDE.md` 참고)

### 4.2 첫 번째 모델 추가하기

예시: **Todo** 기능 추가

#### Step 1: 데이터베이스 모델 생성

`backend/app/models/todo.py`
```python
from sqlalchemy import Column, Integer, String, Boolean, DateTime, ForeignKey
from sqlalchemy.sql import func
from sqlalchemy.orm import relationship
from app.database import Base


class Todo(Base):
    """할 일 모델"""
    __tablename__ = "todos"

    id = Column(Integer, primary_key=True, index=True)
    title = Column(String(255), nullable=False)
    description = Column(String(1000), nullable=True)
    is_completed = Column(Boolean, default=False)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), onupdate=func.now())

    # 관계 설정
    user = relationship("User", back_populates="todos")
```

`backend/app/models/user.py` (수정)
```python
# User 모델에 관계 추가
from sqlalchemy.orm import relationship

class User(Base):
    # ... 기존 컬럼들 ...

    # 관계 추가
    todos = relationship("Todo", back_populates="user")
```

`backend/app/models/__init__.py` (수정)
```python
from app.models.user import User
from app.models.todo import Todo

__all__ = ["User", "Todo"]
```

#### Step 2: Pydantic 스키마 생성

`backend/app/schemas/todo.py`
```python
from pydantic import BaseModel, Field
from typing import Optional
from datetime import datetime


class TodoBase(BaseModel):
    """할 일 기본 스키마"""
    title: str = Field(..., min_length=1, max_length=255)
    description: Optional[str] = Field(None, max_length=1000)
    is_completed: bool = False


class TodoCreate(TodoBase):
    """할 일 생성 스키마"""
    pass


class TodoUpdate(BaseModel):
    """할 일 업데이트 스키마"""
    title: Optional[str] = Field(None, min_length=1, max_length=255)
    description: Optional[str] = None
    is_completed: Optional[bool] = None


class TodoInDB(TodoBase):
    """DB에서 가져온 할 일"""
    id: int
    user_id: int
    created_at: datetime
    updated_at: Optional[datetime] = None

    model_config = {"from_attributes": True}


class Todo(TodoInDB):
    """API 응답용 할 일"""
    pass
```

`backend/app/schemas/__init__.py` (수정)
```python
from app.schemas.user import User, UserCreate, UserUpdate, UserInDB, Token, TokenPayload
from app.schemas.todo import Todo, TodoCreate, TodoUpdate, TodoInDB

__all__ = [
    "User", "UserCreate", "UserUpdate", "UserInDB",
    "Token", "TokenPayload",
    "Todo", "TodoCreate", "TodoUpdate", "TodoInDB"
]
```

#### Step 3: API 엔드포인트 생성

`backend/app/api/v1/todos.py`
```python
from typing import List
from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session

from app.database import get_db
from app.models.user import User
from app.models.todo import Todo
from app.schemas.todo import Todo as TodoSchema, TodoCreate, TodoUpdate
from app.api.deps import get_current_active_user

router = APIRouter()


@router.get("/", response_model=List[TodoSchema])
def get_todos(
    skip: int = 0,
    limit: int = 100,
    current_user: User = Depends(get_current_active_user),
    db: Session = Depends(get_db)
):
    """현재 사용자의 할 일 목록 조회"""
    todos = db.query(Todo).filter(
        Todo.user_id == current_user.id
    ).offset(skip).limit(limit).all()
    return todos


@router.post("/", response_model=TodoSchema, status_code=status.HTTP_201_CREATED)
def create_todo(
    todo_in: TodoCreate,
    current_user: User = Depends(get_current_active_user),
    db: Session = Depends(get_db)
):
    """새 할 일 생성"""
    todo = Todo(
        **todo_in.model_dump(),
        user_id=current_user.id
    )
    db.add(todo)
    db.commit()
    db.refresh(todo)
    return todo


@router.get("/{todo_id}", response_model=TodoSchema)
def get_todo(
    todo_id: int,
    current_user: User = Depends(get_current_active_user),
    db: Session = Depends(get_db)
):
    """특정 할 일 조회"""
    todo = db.query(Todo).filter(
        Todo.id == todo_id,
        Todo.user_id == current_user.id
    ).first()

    if not todo:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Todo not found"
        )
    return todo


@router.put("/{todo_id}", response_model=TodoSchema)
def update_todo(
    todo_id: int,
    todo_in: TodoUpdate,
    current_user: User = Depends(get_current_active_user),
    db: Session = Depends(get_db)
):
    """할 일 수정"""
    todo = db.query(Todo).filter(
        Todo.id == todo_id,
        Todo.user_id == current_user.id
    ).first()

    if not todo:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Todo not found"
        )

    # 업데이트
    update_data = todo_in.model_dump(exclude_unset=True)
    for field, value in update_data.items():
        setattr(todo, field, value)

    db.commit()
    db.refresh(todo)
    return todo


@router.delete("/{todo_id}", status_code=status.HTTP_204_NO_CONTENT)
def delete_todo(
    todo_id: int,
    current_user: User = Depends(get_current_active_user),
    db: Session = Depends(get_db)
):
    """할 일 삭제"""
    todo = db.query(Todo).filter(
        Todo.id == todo_id,
        Todo.user_id == current_user.id
    ).first()

    if not todo:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Todo not found"
        )

    db.delete(todo)
    db.commit()
    return None
```

#### Step 4: 라우터 등록

`backend/app/main.py` (수정)
```python
from app.api.v1 import auth, users, todos  # ← todos 추가

# ... 기존 코드 ...

# API 라우터 등록
app.include_router(auth.router, prefix=f"{settings.API_V1_STR}/auth", tags=["auth"])
app.include_router(users.router, prefix=f"{settings.API_V1_STR}/users", tags=["users"])
app.include_router(todos.router, prefix=f"{settings.API_V1_STR}/todos", tags=["todos"])  # ← 추가
```

#### Step 5: 테스트

**컨테이너 재시작 (모델 변경 반영)**
```bash
# Dev Container 사용 시: 터미널에서
# Ctrl+C로 서버 중지 후 다시 시작

# Docker Compose 사용 시:
docker-compose -f docker-compose.dev.yml restart backend
```

**API 테스트**
```bash
# 1. 로그인
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username": "test", "password": "test1234"}'

# 응답에서 access_token 복사

# 2. Todo 생성
curl -X POST http://localhost:8000/api/v1/todos \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <access_token>" \
  -d '{"title": "첫 번째 할 일", "description": "테스트"}'

# 3. Todo 목록 조회
curl http://localhost:8000/api/v1/todos \
  -H "Authorization: Bearer <access_token>"
```

**Swagger UI로 테스트**
1. http://localhost:8000/docs 접속
2. `/api/v1/auth/login` → Try it out → 로그인
3. 우측 상단 "Authorize" 버튼 → 토큰 입력
4. `/api/v1/todos` 엔드포인트들 테스트

#### Step 6: Git 커밋

```bash
git add .
git commit -m "feat(todos): Todo CRUD API 구현

- Todo 모델 추가 (models/todo.py)
- Todo 스키마 추가 (schemas/todo.py)
- Todo API 엔드포인트 추가 (api/v1/todos.py)
- User-Todo 관계 설정
- CRUD 작업 모두 구현 및 테스트 완료"
```

---

## 5. 개발 워크플로우

### 5.1 일반적인 개발 흐름

```
1. 기능 요구사항 정의
   ↓
2. 데이터베이스 모델 설계 (models/)
   ↓
3. Pydantic 스키마 작성 (schemas/)
   ↓
4. API 엔드포인트 구현 (api/v1/)
   ↓
5. 라우터 등록 (main.py)
   ↓
6. 테스트 (수동 또는 자동)
   ↓
7. Git 커밋
   ↓
8. 프론트엔드 연동 (선택)
```

### 5.2 반복 개발 사이클

```bash
# 1. 새 브랜치 생성
git checkout -b feature/new-feature

# 2. 개발
# - 코드 작성 (컨테이너 안에서)
# - 저장하면 자동 리로드

# 3. 테스트
docker-compose -f docker-compose.dev.yml exec backend pytest

# 4. 커밋
git add .
git commit -m "feat(feature): 새 기능 구현"

# 5. 푸시
git push origin feature/new-feature

# 6. Pull Request 생성 (GitHub/GitLab)

# 7. 머지 후 main으로 돌아오기
git checkout main
git pull origin main
```

### 5.3 데이터베이스 마이그레이션 (Alembic)

**초기 설정 (처음 한 번만)**
```bash
# 컨테이너 안에서
docker-compose -f docker-compose.dev.yml exec backend alembic init alembic

# alembic/env.py 수정
# target_metadata = Base.metadata
# sqlalchemy.url을 config.py의 DATABASE_URL로 변경
```

**마이그레이션 사용**
```bash
# 모델 변경 후 마이그레이션 생성
docker-compose -f docker-compose.dev.yml exec backend \
  alembic revision --autogenerate -m "Add todo model"

# 마이그레이션 적용
docker-compose -f docker-compose.dev.yml exec backend \
  alembic upgrade head

# 롤백
docker-compose -f docker-compose.dev.yml exec backend \
  alembic downgrade -1
```

### 5.4 프론트엔드 연동

**프론트엔드에서 API 호출**

`frontend/MyApp/src/services/todoService.ts`
```typescript
import api from './api';

export interface Todo {
  id: number;
  title: string;
  description?: string;
  is_completed: boolean;
  created_at: string;
}

export interface TodoCreate {
  title: string;
  description?: string;
  is_completed?: boolean;
}

class TodoService {
  async getTodos(): Promise<Todo[]> {
    const response = await api.get<Todo[]>('/api/v1/todos');
    return response.data;
  }

  async createTodo(data: TodoCreate): Promise<Todo> {
    const response = await api.post<Todo>('/api/v1/todos', data);
    return response.data;
  }

  async updateTodo(id: number, data: Partial<TodoCreate>): Promise<Todo> {
    const response = await api.put<Todo>(`/api/v1/todos/${id}`, data);
    return response.data;
  }

  async deleteTodo(id: number): Promise<void> {
    await api.delete(`/api/v1/todos/${id}`);
  }
}

export default new TodoService();
```

---

## 6. 프로젝트 성장시키기

### 6.1 다음 단계들

- [ ] **인증/권한 확장**: Role-based access control (RBAC)
- [ ] **파일 업로드**: S3 또는 로컬 스토리지
- [ ] **이메일 발송**: 비밀번호 재설정, 알림
- [ ] **실시간 기능**: WebSocket (FastAPI)
- [ ] **캐싱**: Redis 추가
- [ ] **배경 작업**: Celery
- [ ] **로깅**: 구조화된 로깅
- [ ] **모니터링**: Sentry, Prometheus
- [ ] **CI/CD**: GitHub Actions, GitLab CI

### 6.2 추가 리소스

- [FastAPI 공식 문서](https://fastapi.tiangolo.com/)
- [SQLAlchemy 문서](https://docs.sqlalchemy.org/)
- [React Native 문서](https://reactnative.dev/)
- [Docker 문서](https://docs.docker.com/)

---

## 💡 팁

### Docker 개발 환경 팁

```bash
# Python 패키지 설치 (컨테이너 안에서)
docker-compose -f docker-compose.dev.yml exec backend pip install requests

# requirements.txt에 추가
docker-compose -f docker-compose.dev.yml exec backend pip freeze | grep requests >> requirements.txt

# 컨테이너 재빌드 (팀원과 공유)
docker-compose -f docker-compose.dev.yml up -d --build backend
```

### 디버깅 팁

```bash
# 로그 실시간 확인
docker-compose -f docker-compose.dev.yml logs -f backend

# 컨테이너 안 셸 접속
docker-compose -f docker-compose.dev.yml exec backend bash

# Python 인터렉티브 셸
docker-compose -f docker-compose.dev.yml exec backend python
>>> from app.database import SessionLocal
>>> db = SessionLocal()
>>> from app.models.user import User
>>> users = db.query(User).all()
```

### 트러블슈팅

**문제: 코드 변경이 반영 안 됨**
```bash
docker-compose -f docker-compose.dev.yml restart backend
```

**문제: 데이터베이스 초기화 필요**
```bash
docker-compose -f docker-compose.dev.yml down -v
docker-compose -f docker-compose.dev.yml up -d
```

**문제: 포트 충돌**
```bash
# docker-compose.dev.yml에서 포트 변경
ports:
  - "8001:8000"  # 8001로 변경
```

---

## 🎉 시작 완료!

이제 템플릿을 실제 프로젝트로 전환하고 첫 기능까지 개발했습니다.

**다음 할 일:**
1. 팀원에게 프로젝트 공유
2. 추가 기능 개발
3. 프론트엔드 연동
4. 테스트 코드 작성
5. CI/CD 파이프라인 구축

**질문이 있다면:**
- `CLAUDE.md`: Claude와 협업 규칙
- `DEVELOPMENT.md`: 개발 환경 가이드
- `README.md`: 프로젝트 개요

행운을 빕니다! 🚀
