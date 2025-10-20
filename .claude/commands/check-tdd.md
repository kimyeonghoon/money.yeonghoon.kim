# TDD 체크리스트 확인

## 🛑 작업 시작 전 체크

다음 질문에 답해주세요:

1. **테스트를 먼저 작성했나요?**
   - [ ] YES: 테스트 작성 → 실패 확인 → 구현 순서
   - [ ] NO: **즉시 중단하고 테스트부터 작성**

2. **현재 커버리지는?**
   ```bash
   # Docker에서 실행:
   docker compose -f docker-compose.dev.yml exec frontend npm test -- --coverage --watchAll=false
   docker compose -f docker-compose.dev.yml exec backend pytest --cov=app
   ```
   - [ ] 80% 이상
   - [ ] 80% 미만: **테스트 추가 필요**

3. **모든 테스트가 통과했나요?**
   - [ ] YES: 다음 단계 진행
   - [ ] NO: **실패 원인 수정**

4. **커밋 전 체크리스트**
   - [ ] 테스트 작성 및 통과
   - [ ] 커버리지 80%+
   - [ ] 타입 힌트 적용
   - [ ] Docstring 작성
   - [ ] Git 컨벤션 준수

## ⚠️ CLAUDE.md 핵심 규칙

**절대 금지:**
- ❌ 테스트 없는 코드
- ❌ 로컬 설치 안내 (Docker 필수)
- ❌ any 타입 (TypeScript)
- ❌ Raw SQL (ORM 사용)
- ❌ 평문 비밀번호
- ❌ 50줄 초과 함수
- ❌ 매직 넘버

**필수 사항:**
- ✅ TDD: Red → Green → Refactor
- ✅ Docker 사용
- ✅ 타입 안전성
- ✅ 보안 First
- ✅ 80% 커버리지

---

이 체크리스트를 확인했으면 작업을 시작하세요.
