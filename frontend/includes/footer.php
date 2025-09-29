
<script>
$(document).ready(function() {
    // Initialize Materialize components
    M.AutoInit();

    // Initialize sidenav
    $('.sidenav').sidenav();

    // AJAX 전역 설정 - 모든 요청에 쿠키 포함
    $.ajaxSetup({
        xhrFields: {
            withCredentials: true
        },
        crossDomain: true
    });
});


// 전역 유틸리티 함수들 (모든 페이지에서 사용 가능)
window.formatMoney = function(amount) {
    if (amount == null || amount === '') return '0원';
    return parseInt(amount).toLocaleString() + '원';
};

window.formatDate = function(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.getFullYear() + '-' +
           String(date.getMonth() + 1).padStart(2, '0') + '-' +
           String(date.getDate()).padStart(2, '0');
};

// 로그아웃 함수
window.logout = function() {
    if (confirm('정말 로그아웃 하시겠습니까?')) {
        $.post({
            url: '/logout.php',
            xhrFields: {
                withCredentials: true
            }
        }).done(function() {
            window.location.href = '/login.php';
        }).fail(function() {
            // POST 실패해도 로그아웃 페이지로 이동
            window.location.href = '/logout.php';
        });
    }
};

</script>

</body>
</html>