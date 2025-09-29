/**
 * 로그인 페이지 JavaScript - 머니매니저 시스템
 *
 * 로그인 페이지의 사용자 인터페이스와 사용자 경험을 향상시키는 스크립트입니다.
 * jQuery와 Materialize CSS를 활용하여 인터랙티브한 로그인 폼을 구현합니다.
 *
 * 주요 기능:
 * - 폼 유효성 검사 (클라이언트 사이드)
 * - 로딩 상태 표시
 * - 키보드 단축키 지원 (Enter키 로그인)
 * - 입력 필드 포커스 효과
 * - 사용자 피드백 (토스트 메시지)
 *
 * 의존성:
 * - jQuery 3.6.0+
 * - Materialize CSS 1.0.0+
 *
 * @package MoneyManager
 * @version 1.0
 * @author YeongHoon Kim
 */

// DOM 준비 완료시 실행
$(document).ready(function() {

    /**
     * Materialize CSS 컴포넌트 초기화
     * 텍스트 필드의 라벨 위치를 올바르게 설정
     */
    M.updateTextFields();

    /**
     * 로그인 폼 제출 이벤트 처리
     * 클라이언트 사이드 유효성 검사와 UX 개선
     */
    $('#login-form').on('submit', function(e) {
        // 입력값 수집
        const email = $('#email').val();
        const password = $('#password').val();

        // 필수 입력값 검증
        if (!email || !password) {
            // 에러 토스트 메시지 표시
            M.toast({
                html: '이메일과 비밀번호를 입력해주세요.',
                classes: 'red'
            });
            e.preventDefault();  // 폼 제출 중단
            return;
        }

        // 로딩 상태 UI 변경
        $('#login-btn').addClass('loading');        // 버튼에 로딩 클래스 추가
        $('.btn-text').text('로그인 중...');        // 버튼 텍스트 변경
        $('.loading').show();                       // 스피너 표시
    });

    /**
     * 키보드 단축키 지원
     * 비밀번호 필드에서 Enter키 입력시 자동 로그인 시도
     */
    $('#password').on('keypress', function(e) {
        if (e.which === 13) {  // Enter키 코드
            $('#login-form').submit();
        }
    });

    /**
     * 입력 필드 포커스 효과
     * 사용자 경험 향상을 위한 시각적 피드백
     */
    $('#email, #password').on('focus', function() {
        // 포커스시 focused 클래스 추가
        $(this).parent().addClass('focused');
    }).on('blur', function() {
        // 포커스 해제시 입력값이 없으면 focused 클래스 제거
        if (!$(this).val()) {
            $(this).parent().removeClass('focused');
        }
    });
});