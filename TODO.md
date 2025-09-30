# 내일 할 일

## 프로덕션 배포 및 테스트
1. **프로덕션 서버 배포**
   - `git pull` 및 `docker compose restart backend-php` 실행
   - ArchiveController 변경사항 반영 필요

2. **9월 아카이브 삭제**
   - 커밋 롤백으로 인해 일부 수정사항 누락되었을 수 있음
   - 배포 후 테스트: `curl -X DELETE 'https://money.yeonghoon.kim/api/archive/delete?month=2025-09' -b /tmp/prod_cookies.txt`
   - 삭제 안되면 코드 확인 필요

3. **Crontab 환경변수 설정**
   ```bash
   crontab -e

   SNAPSHOT_EMAIL=your@email.com
   SNAPSHOT_PASSWORD=yourpassword

   1 0 1 * * /home/kim-yeonghoon/workspace/money.yeonghoon.kim/scripts/create-monthly-snapshot.sh >> /home/kim-yeonghoon/workspace/money.yeonghoon.kim/logs/cron.log 2>&1
   ```
   실제 이메일과 비밀번호로 교체 필요

## 완료된 작업
- ✅ 아카이브 삭제 API 구현 (DELETE /api/archive/delete)
- ✅ 스냅샷 스크립트 자동 로그인 기능 추가
- ✅ 환경변수 기반 인증 방식 적용
- ✅ Git에 비밀번호 업로드 방지 (커밋 롤백 완료)