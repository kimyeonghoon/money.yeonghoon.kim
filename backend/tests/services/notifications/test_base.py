"""
BaseNotificationService 테스트

추상 베이스 클래스의 인터페이스 및 공통 동작을 검증합니다.
"""
import pytest
from abc import ABC


class TestBaseNotificationService:
    """BaseNotificationService 테스트 클래스"""

    def test_base_notification_is_abstract(self):
        """BaseNotificationService는 추상 클래스여야 함"""
        # Given: BaseNotificationService 임포트
        from app.services.notifications.base import BaseNotificationService

        # Then: ABC를 상속받아야 함
        assert issubclass(BaseNotificationService, ABC)

    def test_base_notification_cannot_be_instantiated(self):
        """추상 클래스는 직접 인스턴스화 불가"""
        # Given: BaseNotificationService
        from app.services.notifications.base import BaseNotificationService

        # When & Then: 인스턴스화 시도하면 TypeError 발생
        with pytest.raises(TypeError):
            BaseNotificationService()

    def test_base_notification_has_required_methods(self):
        """필수 추상 메서드가 정의되어 있어야 함"""
        # Given: BaseNotificationService
        from app.services.notifications.base import BaseNotificationService

        # Then: 필수 추상 메서드들이 존재해야 함
        assert hasattr(BaseNotificationService, 'send_login_alert')
        assert hasattr(BaseNotificationService, 'is_enabled')

    def test_concrete_implementation_requires_all_methods(self):
        """구체 클래스는 모든 추상 메서드를 구현해야 함"""
        # Given: BaseNotificationService
        from app.services.notifications.base import BaseNotificationService

        # When: 일부 메서드만 구현한 클래스
        class IncompleteNotifier(BaseNotificationService):
            def is_enabled(self) -> bool:
                return True
            # send_login_alert 미구현

        # Then: 인스턴스화 불가
        with pytest.raises(TypeError):
            IncompleteNotifier()

    @pytest.mark.asyncio
    async def test_concrete_implementation_can_be_instantiated(self):
        """모든 메서드를 구현하면 인스턴스화 가능"""
        # Given: BaseNotificationService
        from app.services.notifications.base import BaseNotificationService

        # When: 모든 메서드를 구현한 클래스
        class CompleteNotifier(BaseNotificationService):
            def is_enabled(self) -> bool:
                return True

            async def send_login_alert(
                self, user_email: str, ip_address: str, user_agent: str
            ) -> bool:
                return True

        # Then: 인스턴스화 가능
        notifier = CompleteNotifier()
        assert notifier is not None
        assert await notifier.send_login_alert("test@example.com", "127.0.0.1", "TestAgent")
