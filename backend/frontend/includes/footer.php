    <!-- Footer -->
    <footer class="page-footer">
        <div class="container">
            <div class="row">
                <div class="col s12 center-align">
                    <p class="white-text">&copy; 2024 개인 자산관리 시스템. All rights reserved.</p>
                    <p class="white-text">스마트한 재무 관리로 더 나은 미래를 만들어보세요.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Fixed Action Button -->
    <div class="fixed-action-btn">
        <a class="btn-floating btn-large waves-effect waves-light">
            <i class="large material-icons">add</i>
        </a>
        <ul>
            <li><a class="btn-floating green tooltipped waves-effect waves-light" data-position="left" data-tooltip="일별지출 추가" href="/frontend/daily-expenses.php"><i class="material-icons">receipt</i></a></li>
            <li><a class="btn-floating orange tooltipped waves-effect waves-light" data-position="left" data-tooltip="현금자산 관리" href="/frontend/cash-assets.php"><i class="material-icons">account_balance_wallet</i></a></li>
            <li><a class="btn-floating red tooltipped waves-effect waves-light" data-position="left" data-tooltip="대시보드" href="/frontend/dashboard.php"><i class="material-icons">dashboard</i></a></li>
        </ul>
    </div>

<script>
$(document).ready(function() {
    // Initialize Materialize components
    M.AutoInit();

    // Initialize sidenav
    $('.sidenav').sidenav();

    // Initialize fixed action button
    $('.fixed-action-btn').floatingActionButton();

    // Initialize tooltips
    $('.tooltipped').tooltip();

    // 현재 시간 표시
    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);

    // 시스템 정보 표시
    updateSystemInfo();

    // 스크롤 버튼 표시/숨김
    $(window).scroll(function() {
        if ($(this).scrollTop() > 300) {
            $('#scroll-top').fadeIn();
        } else {
            $('#scroll-top').fadeOut();
        }
    });
});

function updateCurrentTime() {
    const now = new Date();
    const timeString = now.getFullYear() + '년 ' +
                      String(now.getMonth() + 1).padStart(2, '0') + '월 ' +
                      String(now.getDate()).padStart(2, '0') + '일 ' +
                      String(now.getHours()).padStart(2, '0') + ':' +
                      String(now.getMinutes()).padStart(2, '0') + ':' +
                      String(now.getSeconds()).padStart(2, '0');
    $('#current-time').text('현재시각: ' + timeString);
}

function updateSystemInfo() {
    // 브라우저 정보
    const browserInfo = navigator.userAgent;
    let browser = 'Unknown';

    if (browserInfo.indexOf('Chrome') > -1) browser = 'Chrome';
    else if (browserInfo.indexOf('Firefox') > -1) browser = 'Firefox';
    else if (browserInfo.indexOf('Safari') > -1) browser = 'Safari';
    else if (browserInfo.indexOf('Edge') > -1) browser = 'Edge';

    // 화면 해상도
    const screenInfo = screen.width + 'x' + screen.height;

    // 접속 정보 표시
    const systemInfoHtml =
        '브라우저: ' + browser + ' | ' +
        '해상도: ' + screenInfo + ' | ' +
        '접속시간: ' + new Date().toLocaleTimeString();

    $('#system-info').html(systemInfoHtml);
}

function scrollToTop() {
    $('html, body').animate({
        scrollTop: 0
    }, 500);
}

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

window.showGlobalMessage = function(text, type, duration) {
    duration = duration || 3000;

    // 기존 메시지 제거
    $('#global-message').remove();

    // 새 메시지 생성
    const messageHtml = '<div id="global-message" class="message ' + type + '" ' +
                       'style="position: fixed; top: 20px; right: 20px; z-index: 9999; ' +
                       'max-width: 300px; box-shadow: 3px 3px 8px rgba(0,0,0,0.3);">' +
                       text + '</div>';

    $('body').append(messageHtml);

    // 자동 사라짐
    setTimeout(function() {
        $('#global-message').fadeOut(function() {
            $(this).remove();
        });
    }, duration);
};

// API 호출 공통 함수
window.callAPI = function(url, method, data, successCallback, errorCallback) {
    const options = {
        url: url,
        method: method || 'GET',
        success: successCallback || function() {},
        error: errorCallback || function() {
            showGlobalMessage('서버와의 연결에 실패했습니다.', 'error');
        }
    };

    if (data && (method === 'POST' || method === 'PUT')) {
        options.contentType = 'application/json';
        options.data = JSON.stringify(data);
    } else if (data && method === 'GET') {
        options.data = data;
    }

    $.ajax(options);
};

// 페이지 로드 완료 시 알림
$(window).on('load', function() {
    // 2000년대 느낌의 로딩 완료 효과 (선택사항)
    if (typeof pageLoadComplete === 'function') {
        pageLoadComplete();
    }
});

// 키보드 단축키
$(document).keydown(function(e) {
    // Ctrl+H: 홈(대시보드)으로
    if (e.ctrlKey && e.keyCode === 72) {
        e.preventDefault();
        window.location.href = '/frontend/dashboard.php';
    }
    // ESC: 열려있는 폼 닫기
    else if (e.keyCode === 27) {
        if (typeof hideForm === 'function') {
            hideForm();
        }
    }
});
</script>

</body>
</html>