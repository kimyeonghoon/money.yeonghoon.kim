"""
Base Notification Service

모든 알림 서비스가 상속받아야 하는 추상 베이스 클래스입니다.
새로운 알림 채널을 추가하려면 이 클래스를 상속받아 구현하세요.

Example:
    class EmailNotificationService(BaseNotificationService):
        def is_enabled(self) -> bool:
            return config.EMAIL_ENABLED

        async def send_login_alert(
            self, user_email: str, ip_address: str, user_agent: str
        ) -> bool:
            # 이메일 전송 로직
            return True
"""

from abc import ABC, abstractmethod


class BaseNotificationService(ABC):
    """알림 서비스 추상 베이스 클래스

    모든 알림 서비스는 이 클래스를 상속받아 필수 메서드를 구현해야 합니다.

    필수 구현 메서드:
        - is_enabled(): 서비스 활성화 여부 확인
        - send_login_alert(): 로그인 알림 전송

    Note:
        - 추상 클래스이므로 직접 인스턴스화 불가
        - 모든 메서드를 구현하지 않으면 TypeError 발생
    """

    @abstractmethod
    def is_enabled(self) -> bool:
        """알림 서비스 활성화 여부 확인

        환경 변수나 설정에 따라 이 서비스가 활성화되어 있는지 확인합니다.

        Returns:
            bool: 활성화되어 있으면 True, 아니면 False

        Example:
            >>> notifier = TelegramNotificationService()
            >>> if notifier.is_enabled():
            ...     await notifier.send_login_alert(...)
        """
        pass

    @abstractmethod
    async def send_login_alert(
        self, user_email: str, ip_address: str, user_agent: str
    ) -> bool:
        """로그인 알림 전송

        사용자가 로그인했을 때 알림을 전송합니다.

        Args:
            user_email (str): 로그인한 사용자 이메일
            ip_address (str): 로그인 IP 주소
            user_agent (str): 브라우저/디바이스 정보

        Returns:
            bool: 전송 성공 시 True, 실패 시 False

        Raises:
            Exception: 알림 전송 중 오류 발생 시

        Example:
            >>> notifier = TelegramNotificationService()
            >>> success = await notifier.send_login_alert(
            ...     "user@example.com",
            ...     "192.168.1.1",
            ...     "Mozilla/5.0..."
            ... )
            >>> if success:
            ...     print("알림 전송 성공")

        Note:
            - 알림 전송 실패가 로그인 자체를 방해해서는 안 됨
            - 예외 발생 시 호출하는 쪽에서 catch하여 로깅만 수행
        """
        pass
