# TDD (Test-Driven Development) 가이드

이 문서는 TDD 방식으로 개발하는 방법을 설명합니다.

## 📖 TDD란?

**Test-Driven Development (테스트 주도 개발)**는 테스트를 먼저 작성하고, 그 테스트를 통과하는 코드를 작성하는 개발 방법론입니다.

### TDD 사이클: Red-Green-Refactor

```
🔴 Red    → 실패하는 테스트 작성
   ↓
🟢 Green  → 테스트를 통과하는 최소한의 코드 작성
   ↓
🔵 Refactor → 코드 개선 (테스트는 계속 통과)
   ↓
   반복...
```

## 🎯 TDD의 장점

1. **버그 감소**: 테스트가 먼저 있으므로 버그를 조기에 발견
2. **설계 개선**: 테스트 가능한 코드는 자연스럽게 좋은 설계
3. **리팩토링 안전**: 테스트가 있어 리팩토링 시 안전
4. **문서화**: 테스트가 코드의 사용법을 보여줌
5. **자신감**: 테스트가 통과하면 배포 자신감

## 🚀 TDD로 새 기능 개발하기

### 예시: Todo CRUD API

#### Step 1: 🔴 Red - 테스트 먼저 작성

`backend/tests/api/v1/test_todos.py`

```python
"""
Todo API 테스트 (TDD)
"""
import pytest
from fastapi.testclient import TestClient
from sqlalchemy.orm import Session


class TestCreateTodo:
    """Todo 생성 테스트"""

    def test_create_todo_success(
        self, client: TestClient, auth_headers: dict, db: Session
    ):
        """Todo 생성 성공"""
        # Given: Todo 데이터
        todo_data = {
            "title": "Buy milk",
            "description": "Get 2% milk from store"
        }

        # When: Todo 생성 요청
        response = client.post(
            "/api/v1/todos",
            headers=auth_headers,
            json=todo_data
        )

        # Then: 201 Created
        assert response.status_code == 201
        data = response.json()
        assert data["title"] == todo_data["title"]
        assert data["description"] == todo_data["description"]
        assert data["is_completed"] == False
        assert "id" in data
        assert "user_id" in data
```

**테스트 실행 (실패해야 정상)**

```bash
docker-compose -f docker-compose.dev.yml exec backend pytest tests/api/v1/test_todos.py::TestCreateTodo::test_create_todo_success -v

# 결과: FAILED (404 Not Found - 엔드포인트가 아직 없음)
```

#### Step 2: 🟢 Green - 테스트 통과시키기

**2.1 모델 생성**

`backend/app/models/todo.py`

```python
from sqlalchemy import Column, Integer, String, Boolean, DateTime, ForeignKey
from sqlalchemy.sql import func
from sqlalchemy.orm import relationship
from app.database import Base


class Todo(Base):
    __tablename__ = "todos"

    id = Column(Integer, primary_key=True, index=True)
    title = Column(String(255), nullable=False)
    description = Column(String(1000), nullable=True)
    is_completed = Column(Boolean, default=False)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), onupdate=func.now())

    user = relationship("User", back_populates="todos")
```

`backend/app/models/user.py` (수정)

```python
# User 클래스에 추가
todos = relationship("Todo", back_populates="user")
```

**2.2 스키마 생성**

`backend/app/schemas/todo.py`

```python
from pydantic import BaseModel, Field
from typing import Optional
from datetime import datetime


class TodoCreate(BaseModel):
    title: str = Field(..., min_length=1, max_length=255)
    description: Optional[str] = Field(None, max_length=1000)


class Todo(BaseModel):
    id: int
    title: str
    description: Optional[str]
    is_completed: bool
    user_id: int
    created_at: datetime
    updated_at: Optional[datetime] = None

    model_config = {"from_attributes": True}
```

**2.3 API 엔드포인트 생성**

`backend/app/api/v1/todos.py`

```python
from fastapi import APIRouter, Depends, status
from sqlalchemy.orm import Session

from app.database import get_db
from app.models.user import User
from app.models.todo import Todo
from app.schemas.todo import Todo as TodoSchema, TodoCreate
from app.api.deps import get_current_active_user

router = APIRouter()


@router.post("/", response_model=TodoSchema, status_code=status.HTTP_201_CREATED)
def create_todo(
    todo_in: TodoCreate,
    current_user: User = Depends(get_current_active_user),
    db: Session = Depends(get_db)
):
    """새 Todo 생성"""
    todo = Todo(
        **todo_in.model_dump(),
        user_id=current_user.id
    )
    db.add(todo)
    db.commit()
    db.refresh(todo)
    return todo
```

**2.4 라우터 등록**

`backend/app/main.py`

```python
from app.api.v1 import auth, users, todos

app.include_router(todos.router, prefix=f"{settings.API_V1_STR}/todos", tags=["todos"])
```

**테스트 다시 실행 (통과해야 함)**

```bash
docker-compose -f docker-compose.dev.yml exec backend pytest tests/api/v1/test_todos.py::TestCreateTodo::test_create_todo_success -v

# 결과: PASSED ✓
```

#### Step 3: 🔵 Refactor - 코드 개선

테스트는 계속 통과하면서 코드를 개선합니다.

```python
# 개선 예시: 중복 체크 추가
@router.post("/", response_model=TodoSchema, status_code=status.HTTP_201_CREATED)
def create_todo(
    todo_in: TodoCreate,
    current_user: User = Depends(get_current_active_user),
    db: Session = Depends(get_db)
):
    """새 Todo 생성"""
    # 같은 제목의 Todo가 이미 있는지 확인
    existing = db.query(Todo).filter(
        Todo.user_id == current_user.id,
        Todo.title == todo_in.title,
        Todo.is_completed == False
    ).first()

    if existing:
        raise HTTPException(
            status_code=400,
            detail="Active todo with this title already exists"
        )

    todo = Todo(
        **todo_in.model_dump(),
        user_id=current_user.id
    )
    db.add(todo)
    db.commit()
    db.refresh(todo)
    return todo
```

**리팩토링 후 테스트 확인**

```bash
docker-compose -f docker-compose.dev.yml exec backend pytest tests/api/v1/test_todos.py -v

# 모든 테스트 통과 확인
```

#### Step 4: 추가 테스트 케이스 작성

```python
class TestCreateTodo:
    """Todo 생성 테스트"""

    def test_create_todo_success(self, client, auth_headers, db):
        """Todo 생성 성공"""
        # ... (위에서 작성한 테스트)

    def test_create_todo_duplicate_title(self, client, auth_headers, db, test_user):
        """같은 제목의 활성 Todo가 있을 때 실패"""
        # Given: 이미 존재하는 Todo
        from app.models.todo import Todo
        existing_todo = Todo(
            title="Buy milk",
            user_id=test_user.id
        )
        db.add(existing_todo)
        db.commit()

        # When: 같은 제목으로 생성 시도
        response = client.post(
            "/api/v1/todos",
            headers=auth_headers,
            json={"title": "Buy milk"}
        )

        # Then: 400 Bad Request
        assert response.status_code == 400

    def test_create_todo_empty_title(self, client, auth_headers):
        """빈 제목으로 생성 시도"""
        # Given: 빈 제목
        todo_data = {"title": ""}

        # When: Todo 생성 요청
        response = client.post(
            "/api/v1/todos",
            headers=auth_headers,
            json=todo_data
        )

        # Then: 422 Validation Error
        assert response.status_code == 422

    def test_create_todo_unauthorized(self, client):
        """인증 없이 Todo 생성 시도"""
        # Given: 인증 토큰 없음
        todo_data = {"title": "Buy milk"}

        # When: Todo 생성 요청
        response = client.post("/api/v1/todos", json=todo_data)

        # Then: 401 Unauthorized
        assert response.status_code == 401
```

### 다른 CRUD 작업도 같은 방식으로

**Read (조회)**

```python
# 1. 테스트 작성
class TestGetTodos:
    def test_get_todos_success(self, client, auth_headers):
        # ...

# 2. 엔드포인트 구현
@router.get("/", response_model=List[TodoSchema])
def get_todos(...):
    # ...

# 3. 리팩토링
```

**Update (수정)**

```python
# 1. 테스트 작성
class TestUpdateTodo:
    def test_update_todo_success(self, client, auth_headers):
        # ...

# 2. 엔드포인트 구현
@router.put("/{todo_id}", response_model=TodoSchema)
def update_todo(...):
    # ...

# 3. 리팩토링
```

**Delete (삭제)**

```python
# 1. 테스트 작성
class TestDeleteTodo:
    def test_delete_todo_success(self, client, auth_headers):
        # ...

# 2. 엔드포인트 구현
@router.delete("/{todo_id}", status_code=204)
def delete_todo(...):
    # ...

# 3. 리팩토링
```

## 🧪 테스트 작성 가이드

### Given-When-Then 패턴

모든 테스트는 다음 패턴을 따릅니다:

```python
def test_example(self, client, auth_headers):
    # Given: 초기 상태 설정
    user_data = {"username": "test"}

    # When: 테스트할 동작 수행
    response = client.post("/users", json=user_data)

    # Then: 결과 검증
    assert response.status_code == 201
    assert response.json()["username"] == "test"
```

### 테스트 네이밍 규칙

```python
# 패턴: test_<동작>_<조건>_<결과>
def test_create_user_with_valid_data_success(self):
    """올바른 데이터로 사용자 생성 시 성공"""
    pass

def test_create_user_with_duplicate_email_fails(self):
    """중복 이메일로 사용자 생성 시 실패"""
    pass

def test_login_with_wrong_password_returns_401(self):
    """잘못된 비밀번호로 로그인 시 401 반환"""
    pass
```

### 테스트 클래스 구조

```python
class TestResourceName:
    """리소스 테스트 - 성공 케이스"""

    def test_action_success(self):
        """기본 성공 케이스"""
        pass


class TestResourceNameEdgeCases:
    """리소스 테스트 - 엣지 케이스"""

    def test_action_with_empty_data(self):
        """빈 데이터 처리"""
        pass

    def test_action_with_max_length(self):
        """최대 길이 처리"""
        pass


class TestResourceNameErrors:
    """리소스 테스트 - 에러 케이스"""

    def test_action_unauthorized(self):
        """인증 없이 접근"""
        pass

    def test_action_not_found(self):
        """존재하지 않는 리소스"""
        pass
```

## 🛠️ 테스트 실행

### 전체 테스트 실행

```bash
# 모든 테스트
docker-compose -f docker-compose.dev.yml exec backend pytest

# 상세 출력
docker-compose -f docker-compose.dev.yml exec backend pytest -v

# 실패 시 즉시 중단
docker-compose -f docker-compose.dev.yml exec backend pytest -x
```

### 특정 테스트 실행

```bash
# 특정 파일
docker-compose -f docker-compose.dev.yml exec backend pytest tests/api/v1/test_todos.py

# 특정 클래스
docker-compose -f docker-compose.dev.yml exec backend pytest tests/api/v1/test_todos.py::TestCreateTodo

# 특정 테스트
docker-compose -f docker-compose.dev.yml exec backend pytest tests/api/v1/test_todos.py::TestCreateTodo::test_create_todo_success

# 키워드로 검색
docker-compose -f docker-compose.dev.yml exec backend pytest -k "create"
```

### 커버리지 확인

```bash
# 커버리지 측정
docker-compose -f docker-compose.dev.yml exec backend pytest --cov=app --cov-report=html

# HTML 리포트 확인
# backend/htmlcov/index.html 열기
```

### Watch 모드 (자동 재실행)

```bash
# pytest-watch 설치 (Dockerfile.dev에 추가됨)
docker-compose -f docker-compose.dev.yml exec backend ptw -- -v

# 파일 변경 시 자동으로 테스트 재실행
```

## 📝 테스트 작성 체크리스트

새 기능을 개발할 때:

- [ ] **성공 케이스**: 정상적인 입력으로 예상 결과가 나오는가?
- [ ] **실패 케이스**: 잘못된 입력 시 적절한 에러가 발생하는가?
- [ ] **인증/권한**: 인증 없이 접근 시 401이 반환되는가?
- [ ] **Not Found**: 존재하지 않는 리소스 접근 시 404가 반환되는가?
- [ ] **검증**: 입력 검증이 제대로 작동하는가?
- [ ] **중복**: 중복 생성 시 적절히 처리되는가?
- [ ] **엣지 케이스**: 빈 값, 최대값, 특수문자 등이 처리되는가?

## 🎓 TDD 모범 사례

### DO ✅

1. **테스트를 먼저 작성**: 항상 Red → Green → Refactor
2. **작은 단위로**: 한 번에 하나의 기능만 테스트
3. **명확한 이름**: 테스트 이름만 봐도 무엇을 테스트하는지 알 수 있게
4. **독립적인 테스트**: 테스트 간 의존성 없음
5. **빠른 피드백**: 테스트는 빠르게 실행되어야 함

### DON'T ❌

1. **테스트 건너뛰기**: 코드 먼저 작성 후 테스트 나중에
2. **복잡한 테스트**: 테스트 자체가 이해하기 어려움
3. **여러 것을 한 번에**: 하나의 테스트에서 너무 많은 것을 검증
4. **테스트 무시**: 실패하는 테스트를 skip하거나 제거
5. **외부 의존성**: 실제 DB, 외부 API 호출 (Mock 사용)

## 📊 TDD 워크플로우 요약

```
새 기능 요구사항
    ↓
┌─────────────────────────────────────┐
│  🔴 Red: 실패하는 테스트 작성        │
│  - Given-When-Then 패턴              │
│  - 명확한 테스트 케이스               │
└─────────────────────────────────────┘
    ↓
┌─────────────────────────────────────┐
│  🟢 Green: 테스트 통과시키기          │
│  1. 모델 생성                        │
│  2. 스키마 생성                       │
│  3. API 엔드포인트 구현               │
│  4. 라우터 등록                       │
└─────────────────────────────────────┘
    ↓
┌─────────────────────────────────────┐
│  🔵 Refactor: 코드 개선               │
│  - 중복 제거                         │
│  - 가독성 향상                        │
│  - 성능 최적화                        │
│  (테스트는 계속 통과)                 │
└─────────────────────────────────────┘
    ↓
┌─────────────────────────────────────┐
│  추가 테스트 케이스 작성              │
│  - 에러 케이스                        │
│  - 엣지 케이스                        │
│  - 권한 케이스                        │
└─────────────────────────────────────┘
    ↓
   Git 커밋
    ↓
  다음 기능...
```

## 🚀 시작하기

1. **템플릿의 기존 테스트 실행**
   ```bash
   docker-compose -f docker-compose.dev.yml exec backend pytest -v
   ```

2. **첫 TDD 개발 시도**
   - `GETTING_STARTED.md`의 Todo 예시를 TDD 방식으로 따라하기
   - Red → Green → Refactor 사이클 경험

3. **팀 규칙 정하기**
   - 테스트 커버리지 목표 (예: 80% 이상)
   - PR 시 테스트 통과 필수
   - 코드 리뷰 시 테스트 확인

---

**Happy TDD! 🎉**
