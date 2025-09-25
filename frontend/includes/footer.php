
<script>
$(document).ready(function() {
    // Initialize Materialize components
    M.AutoInit();

    // Initialize sidenav
    $('.sidenav').sidenav();
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

</script>

</body>
</html>