# 📚 문서 목록

이 폴더에는 프로젝트 개발에 필요한 모든 문서가 있습니다.

## 🚀 시작하기

### 필수 문서 (처음 읽어야 할 문서)

1. **[GETTING_STARTED.md](GETTING_STARTED.md)** ⭐
   - 템플릿으로 새 프로젝트 시작하기
   - 첫 기능 개발 (Todo CRUD 예시)
   - 단계별 가이드

2. **[TDD.md](TDD.md)** ⭐
   - TDD (테스트 주도 개발) 가이드
   - Red-Green-Refactor 사이클
   - Given-When-Then 패턴
   - 실전 예제

## 🐳 Docker 개발

### Docker가 필수인 이유

3. **[WHY_DOCKER.md](WHY_DOCKER.md)** 🐳
   - "제 컴퓨터에서는 되는데요?" 문제 해결
   - 로컬 개발 vs Docker 개발 비교
   - 환경 일치 보장
   - 의존성 격리

### 개발 환경 설정

4. **[DEVELOPMENT.md](DEVELOPMENT.md)**
   - Docker 개발 환경 가이드
   - VS Code Dev Container 사용법
   - 디버깅 방법
   - 유용한 명령어

5. **[WIFI_DEBUGGING.md](WIFI_DEBUGGING.md)** 📱
   - WiFi 디버깅으로 완전 Docker 개발
   - Android 기기 연결 방법
   - 에뮬레이터 설정
   - 문제 해결
   - **Node.js 로컬 설치 없이 100% Docker 개발!**

## 📋 참고 자료

6. **[ARCHITECTURE.md](ARCHITECTURE.md)** 🏗️
   - 시스템 아키텍처 다이어그램
   - 요청 처리 플로우
   - 인증 플로우
   - Docker 컨테이너 구조
   - 레이어 아키텍처

7. **[DATABASE_SCHEMA.md](DATABASE_SCHEMA.md)** 🗄️
   - 데이터베이스 ER 다이어그램
   - 테이블 상세 정보
   - 인덱스 및 제약 조건
   - 쿼리 패턴
   - 성능 최적화 가이드

8. **[VERSIONS.md](VERSIONS.md)**
   - 사용 중인 패키지 버전
   - 최신 버전 정보
   - 업데이트 로그

9. **[SECURITY.md](SECURITY.md)** 🔐
   - 민감 정보 관리
   - Pre-commit hook 설정
   - 보안 검사 도구
   - 이미 커밋된 민감 정보 제거

## 📖 문서 읽는 순서 (추천)

### 신규 프로젝트 시작

```
1. WHY_DOCKER.md
   ↓ (Docker가 왜 필수인지 이해)

2. GETTING_STARTED.md
   ↓ (프로젝트 초기화 및 설정)

3. WIFI_DEBUGGING.md
   ↓ (완전 Docker 개발 환경 설정)

4. TDD.md
   ↓ (TDD로 첫 기능 개발)

5. DEVELOPMENT.md
   (필요할 때 참고)
```

### 기존 프로젝트 작업

```
1. TDD.md
   ↓ (테스트 주도 개발)

2. DEVELOPMENT.md
   ↓ (개발 환경 참고)

3. WIFI_DEBUGGING.md
   (문제 발생 시 참고)
```

## 🔗 빠른 링크

### 자주 찾는 내용

| 주제 | 문서 | 섹션 |
|------|------|------|
| 프로젝트 시작 | [GETTING_STARTED.md](GETTING_STARTED.md) | 템플릿 복사 및 초기화 |
| TDD 사이클 | [TDD.md](TDD.md) | Red-Green-Refactor |
| WiFi adb 연결 | [WIFI_DEBUGGING.md](WIFI_DEBUGGING.md) | Android 기기 설정 |
| Dev Container | [DEVELOPMENT.md](DEVELOPMENT.md) | VS Code에서 디버깅 |
| Docker 이유 | [WHY_DOCKER.md](WHY_DOCKER.md) | 로컬 개발의 문제점 |
| 시스템 구조 | [ARCHITECTURE.md](ARCHITECTURE.md) | 전체 아키텍처 다이어그램 |
| DB 스키마 | [DATABASE_SCHEMA.md](DATABASE_SCHEMA.md) | ER 다이어그램 및 테이블 정의 |
| 패키지 버전 | [VERSIONS.md](VERSIONS.md) | 전체 목록 |
| 보안 가이드 | [SECURITY.md](SECURITY.md) | 민감 정보 관리 |

## 💡 문서 작성 규칙

새 문서를 추가할 때:

1. **명확한 제목**: 내용이 한눈에 파악되도록
2. **목차 제공**: 긴 문서는 반드시 목차 추가
3. **코드 예시**: 실제 동작하는 코드 포함
4. **단계별 설명**: 1, 2, 3... 단계로 구분
5. **문제 해결 섹션**: 자주 발생하는 문제와 해결 방법
6. **관련 문서 링크**: 다른 문서와 연결

## 🔍 검색 팁

### 키워드로 문서 찾기

- **Docker 설정**: DEVELOPMENT.md, WHY_DOCKER.md
- **테스트 작성**: TDD.md
- **WiFi 연결**: WIFI_DEBUGGING.md
- **프로젝트 시작**: GETTING_STARTED.md
- **시스템 구조**: ARCHITECTURE.md
- **데이터베이스**: DATABASE_SCHEMA.md
- **버전 확인**: VERSIONS.md
- **보안**: SECURITY.md

### IDE에서 검색

VS Code에서 `Ctrl+Shift+F` (또는 `Cmd+Shift+F`)로 전체 문서 검색 가능

## 📝 문서 업데이트

문서를 수정한 경우:

1. 변경사항을 명확히 기술
2. 예시 코드도 함께 업데이트
3. 관련 문서도 확인하여 일관성 유지
4. 마지막 업데이트 날짜 표시 (문서 하단)

## 🤝 기여

문서 개선 제안:
- 오타, 잘못된 정보 발견 시 이슈 등록
- 더 나은 설명이 있다면 PR 제출
- 새로운 주제가 필요하다면 제안

---

**궁금한 점이 있다면 [../README.md](../README.md)의 "지원" 섹션을 참고하세요.**
