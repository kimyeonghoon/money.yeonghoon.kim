# Alembic 마이그레이션 가이드

이 디렉토리는 데이터베이스 스키마 마이그레이션을 관리합니다.

## 사용법

### 1. 새 마이그레이션 생성

모델을 변경한 후 자동으로 마이그레이션을 생성합니다:

```bash
# Docker 환경에서
docker compose -f docker-compose.dev.yml exec backend alembic revision --autogenerate -m "Add profile_image field"
```

### 2. 마이그레이션 적용

```bash
# 최신 버전으로 업그레이드
docker compose -f docker-compose.dev.yml exec backend alembic upgrade head

# 특정 버전으로 업그레이드
docker compose -f docker-compose.dev.yml exec backend alembic upgrade <revision_id>
```

### 3. 마이그레이션 롤백

```bash
# 이전 버전으로 다운그레이드
docker compose -f docker-compose.dev.yml exec backend alembic downgrade -1

# 특정 버전으로 다운그레이드
docker compose -f docker-compose.dev.yml exec backend alembic downgrade <revision_id>
```

### 4. 현재 상태 확인

```bash
# 현재 마이그레이션 상태
docker compose -f docker-compose.dev.yml exec backend alembic current

# 마이그레이션 히스토리
docker compose -f docker-compose.dev.yml exec backend alembic history
```

## 주의사항

1. **자동 생성 후 확인 필수**: `--autogenerate`로 생성된 마이그레이션은 반드시 수동으로 검토하세요.
2. **롤백 테스트**: 프로덕션 적용 전 개발 환경에서 upgrade/downgrade를 모두 테스트하세요.
3. **Git 커밋**: 마이그레이션 파일은 반드시 Git에 커밋하세요.
4. **순서 유지**: 마이그레이션은 순서대로 적용되므로 절대 삭제하거나 수정하지 마세요.

## 파일 구조

```
alembic/
├── versions/          # 마이그레이션 파일들
│   └── 15ba7ab62cc0_initial_migration.py
├── env.py            # 환경 설정 (app.config, models import)
├── script.py.mako    # 마이그레이션 템플릿
└── README.md         # 이 파일
```

## 트러블슈팅

### "Target database is not up to date"

```bash
# 강제로 현재 버전 기록
docker compose -f docker-compose.dev.yml exec backend alembic stamp head
```

### 모델 변경사항이 감지되지 않음

`alembic/env.py`에 모델이 import되어 있는지 확인:

```python
from app.models import user  # 모델 import 필수
```
