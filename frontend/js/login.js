// Login Page JavaScript
$(document).ready(function() {
    // Materialize 초기화
    M.updateTextFields();

    // 로그인 폼 제출 처리
    $('#login-form').on('submit', function(e) {
        const email = $('#email').val();
        const password = $('#password').val();

        if (!email || !password) {
            M.toast({html: '이메일과 비밀번호를 입력해주세요.', classes: 'red'});
            e.preventDefault();
            return;
        }

        // 로딩 상태 표시
        $('#login-btn').addClass('loading');
        $('.btn-text').text('로그인 중...');
        $('.loading').show();
    });

    // 엔터키로 로그인
    $('#password').on('keypress', function(e) {
        if (e.which === 13) {
            $('#login-form').submit();
        }
    });

    // 입력 필드 포커스 효과
    $('#email, #password').on('focus', function() {
        $(this).parent().addClass('focused');
    }).on('blur', function() {
        if (!$(this).val()) {
            $(this).parent().removeClass('focused');
        }
    });
});