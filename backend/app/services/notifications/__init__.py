"""
Notification services module

다양한 알림 채널을 지원하는 플러그인 방식의 알림 시스템입니다.

Usage:
    from app.services.notifications import NotificationFactory

    notifier = NotificationFactory.get_notifier('telegram')
    await notifier.send_login_alert(user, ip_address, user_agent)
"""

__all__ = []
