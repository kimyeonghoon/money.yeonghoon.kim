"""
애플리케이션 전역 상수 정의

이 모듈은 매직 넘버와 매직 문자열을 상수로 정의하여 코드 품질과 유지보수성을 향상시킵니다.

사용 이유:
1. 가독성: 숫자 100000 대신 VERIFICATION_CODE_MIN을 보면 의미가 명확함
2. 유지보수: 한 곳에서 변경하면 모든 곳에 적용됨
3. 오타 방지: IDE의 자동완성으로 상수명 오타 방지
4. 문서화: 상수명 자체가 문서 역할

참고: CLAUDE.md에서 모든 매직 넘버는 상수화를 요구함
"""

# 인증 코드 (2FA Verification Code)
VERIFICATION_CODE_MIN = 100000
VERIFICATION_CODE_MAX = 999999
VERIFICATION_CODE_LENGTH = 6
VERIFICATION_CODE_EXPIRE_MINUTES = 5

# Telegram 알림
TELEGRAM_API_TIMEOUT = 10.0  # seconds
MAX_USER_AGENT_LENGTH = 50  # User-Agent 문자열 최대 표시 길이

# 비밀번호 (schemas/user.py에서도 사용)
MIN_PASSWORD_LENGTH = 8
MAX_PASSWORD_LENGTH = 100

# 사용자명
MIN_USERNAME_LENGTH = 3
MAX_USERNAME_LENGTH = 100

# 이메일
MAX_EMAIL_LENGTH = 255

# 데이터베이스
MAX_FULLNAME_LENGTH = 255
