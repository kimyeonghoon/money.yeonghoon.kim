<?php
$pageTitle = '현금자산';
include 'includes/header.php';
?>

    <main class="container">
        <div class="section">
            <div class="row">
                <div class="col s12">
                    <h4 class="section-title"><i class="material-icons left">account_balance_wallet</i>현금자산 관리</h4>
                </div>
            </div>

            <!-- 추가/수정 폼 -->
            <div id="asset-form" class="card" style="display: none;">
                <div class="card-content">
                    <span class="card-title" id="form-title">현금자산 추가</span>
                    <form>
                        <div class="row">
                            <div class="input-field col s12 m6">
                                <select id="asset-type">
                                    <option value="" disabled selected>선택하세요</option>
                                    <option value="현금">현금</option>
                                    <option value="예금">예금</option>
                                    <option value="적금">적금</option>
                                    <option value="체크카드">체크카드</option>
                                    <option value="기타">기타</option>
                                </select>
                                <label>구분*</label>
                            </div>
                            <div class="input-field col s12 m6">
                                <input id="bank-name" type="text" class="validate" required>
                                <label for="bank-name">은행/기관*</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12 m6">
                                <input id="item-name" type="text" class="validate" required>
                                <label for="item-name">항목명*</label>
                            </div>
                            <div class="input-field col s12 m6">
                                <input id="balance" type="number" class="validate" required>
                                <label for="balance">잔액* (원)</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12">
                                <input id="notes" type="text">
                                <label for="notes">비고</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-action">
                    <button id="save-btn" class="btn waves-effect waves-light">
                        <i class="material-icons left">save</i>저장
                    </button>
                    <button id="cancel-btn" class="btn grey waves-effect waves-light">
                        <i class="material-icons left">cancel</i>취소
                    </button>
                </div>
            </div>

            <!-- 액션 버튼 -->
            <div class="row">
                <div class="col s12">
                    <button id="add-asset-btn" class="btn waves-effect waves-light">
                        <i class="material-icons left">add</i>새 자산 추가
                    </button>
                    <button id="refresh-btn" class="btn grey waves-effect waves-light">
                        <i class="material-icons left">refresh</i>새로고침
                    </button>
                </div>
            </div>

            <!-- 자산 목록 테이블 -->
            <div class="row">
                <div class="col s12">
                    <div id="loading" class="center-align">
                        <div class="preloader-wrapper active">
                            <div class="spinner-layer spinner-blue-only">
                                <div class="circle-clipper left">
                                    <div class="circle"></div>
                                </div>
                                <div class="gap-patch">
                                    <div class="circle"></div>
                                </div>
                                <div class="circle-clipper right">
                                    <div class="circle"></div>
                                </div>
                            </div>
                        </div>
                        <p>현금자산 목록을 불러오는 중...</p>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="card desktop-table" id="assets-table-card" style="display: none;">
                        <div class="card-content">
                            <div class="responsive-table">
                                <table class="striped">
                                    <thead>
                                        <tr>
                                            <th>구분</th>
                                            <th>은행/기관</th>
                                            <th>항목명</th>
                                            <th>잔액</th>
                                            <th>비고</th>
                                            <th>수정일</th>
                                            <th>관리</th>
                                        </tr>
                                    </thead>
                                    <tbody id="assets-table-body">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="mobile-cards" id="assets-cards-container" style="display: none;">
                    </div>

                    <div id="no-data" class="card grey lighten-4" style="display: none;">
                        <div class="card-content center-align">
                            <span class="card-title grey-text">
                                <i class="material-icons large">account_balance_wallet</i>
                            </span>
                            <p class="grey-text">등록된 현금자산이 없습니다.</p>
                            <p class="grey-text">새 자산을 추가해보세요! 💰</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 총합 표시 -->
            <div class="row">
                <div class="col s12">
                    <div class="card blue lighten-4">
                        <div class="card-content center-align">
                            <span class="card-title blue-text text-darken-2">총 현금자산</span>
                            <h4 id="total-amount" class="orange-text text-darken-2">-</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 메시지 영역 -->
            <div id="message" class="card" style="display: none;">
                <div class="card-content">
                    <span id="message-text"></span>
                </div>
            </div>
        </div>
    </main>

<script>
let editingAssetId = null;

$(document).ready(function() {
    console.log('📱 Document ready, loadAssets 호출 예정');
    loadAssets();

    // 이벤트 핸들러
    $('#add-asset-btn').click(showAddForm);
    $('#cancel-btn').click(hideForm);
    $('#save-btn').click(saveAsset);
    $('#refresh-btn').click(loadAssets);

    // 5초 후 테이블 상태 확인
    setTimeout(function() {
        console.log('⏰ 5초 후 테이블 상태:', $('#assets-table-card').is(':visible') ? '보임' : '숨김');
        console.log('⏰ 5초 후 테이블 내용 개수:', $('#assets-table-body tr').length);
    }, 5000);
});

function loadAssets() {
    console.log('🔄 loadAssets 시작');
    $('#loading').show();
    $('#assets-table-card').hide();
    $('#no-data').hide();

    $.ajax({
        url: '/api/cash-assets',
        method: 'GET',
        success: function(response) {
            console.log('✅ API 응답:', response);
            $('#loading').hide();
            if (response.success) {
                console.log('📊 데이터 개수:', response.data.length);
                if (response.data.length === 0) {
                    console.log('❌ 데이터 없음');
                    $('#no-data').show();
                    $('#total-amount').text('0원');
                } else {
                    console.log('🔨 displayAssets 호출');
                    displayAssets(response.data);
                    displayMobileCards(response.data);
                    calculateTotal(response.data);
                    console.log('👁️ 테이블 및 카드 표시');
                    $('#assets-table-card').show();
                    $('#assets-cards-container').show();
                }
            } else {
                console.log('❌ API 오류:', response.message);
                showMessage('데이터 로드 실패: ' + response.message, 'error');
            }
        },
        error: function() {
            console.log('💥 AJAX 오류');
            $('#loading').hide();
            showMessage('서버와의 연결에 실패했습니다.', 'error');
        }
    });
}

function displayAssets(assets) {
    console.log('🏗️ displayAssets 시작, 자산 개수:', assets.length);
    let tbody = $('#assets-table-body');
    console.log('🔍 tbody 선택됨:', tbody.length, 'elements');
    tbody.empty();
    console.log('🗑️ 테이블 내용 비움');

    // 강제로 테이블 표시
    $('#assets-table-card').show().css('display', 'block');
    tbody.show().css({
        'display': 'table-row-group',
        'visibility': 'visible',
        'opacity': '1'
    });

    assets.forEach(function(asset, index) {
        console.log('➕ 자산 추가 중:', index + 1, asset.item_name);

        // jQuery 객체로 생성하고 강제 스타일 적용
        let $row = $('<tr></tr>').css({
            'background-color': 'white !important',
            'display': 'table-row !important',
            'visibility': 'visible !important',
            'opacity': '1 !important'
        });

        $row.html('<td style="color: #424242 !important; display: table-cell !important;">' + (asset.type || '-') + '</td>' +
                  '<td style="color: #424242 !important; display: table-cell !important;">' + (asset.account_name || asset.bank_name || '-') + '</td>' +
                  '<td style="color: #424242 !important; display: table-cell !important;">' + (asset.item_name || '-') + '</td>' +
                  '<td style="font-weight: bold; color: #cc6600 !important; display: table-cell !important;">' + formatMoney(asset.balance) + '</td>' +
                  '<td style="color: #424242 !important; display: table-cell !important;">' + (asset.notes || '-') + '</td>' +
                  '<td style="color: #424242 !important; display: table-cell !important;">' + formatDate(asset.updated_at || asset.created_at) + '</td>' +
                  '<td style="display: table-cell !important;">' +
                  '<button onclick="editAsset(' + asset.id + ')" class="btn-small waves-effect waves-light blue" style="margin-right: 5px;"><i class="material-icons left">edit</i>수정</button>' +
                  '<button onclick="deleteAsset(' + asset.id + ')" class="btn-small waves-effect waves-light red"><i class="material-icons left">delete</i>삭제</button>' +
                  '</td>');

        tbody.append($row);

        // 추가 후 다시 한번 강제 스타일 적용
        $row.find('td').css({
            'color': '#424242 !important',
            'display': 'table-cell !important',
            'visibility': 'visible !important',
            'opacity': '1 !important'
        });
    });

    // 전체 테이블 강제 표시
    setTimeout(function() {
        console.log('🔍 1초 후 강제 표시');
        $('#assets-table-body, #assets-table-body tr, #assets-table-body td').css({
            'display': 'table-row-group !important',
            'visibility': 'visible !important',
            'opacity': '1 !important'
        });
        $('#assets-table-body tr').css('display', 'table-row !important');
        $('#assets-table-body td').css({
            'display': 'table-cell !important',
            'color': '#424242 !important'
        });
    }, 1000);

    console.log('✅ displayAssets 완료, tbody HTML 길이:', tbody.html().length);
}

function displayMobileCards(assets) {
    console.log('📱 displayMobileCards 시작, 자산 개수:', assets.length);
    let container = $('#assets-cards-container');
    container.empty();

    assets.forEach(function(asset, index) {
        console.log('🃏 카드 생성 중:', index + 1, asset.item_name);

        let typeIcon = getTypeIcon(asset.type);
        let card = $(`
            <div class="mobile-card">
                <div class="mobile-card-header">
                    <div class="mobile-card-title">
                        <i class="material-icons mobile-card-icon">${typeIcon}</i>
                        ${asset.item_name || '-'}
                    </div>
                </div>
                <div class="mobile-card-amount">
                    ${formatMoney(asset.balance)}
                </div>
                <div class="mobile-card-meta">
                    <span><strong>${asset.type || '-'}</strong> | ${asset.account_name || asset.bank_name || '-'}</span>
                    <span>${formatDate(asset.updated_at || asset.created_at)}</span>
                </div>
                <div class="mobile-card-meta">
                    <span>📝 ${asset.notes || '메모 없음'}</span>
                </div>
                <div class="mobile-card-actions">
                    <button onclick="editAsset(${asset.id})" class="btn-small waves-effect waves-light blue">
                        <i class="material-icons left">edit</i>수정
                    </button>
                    <button onclick="deleteAsset(${asset.id})" class="btn-small waves-effect waves-light red">
                        <i class="material-icons left">delete</i>삭제
                    </button>
                </div>
            </div>
        `);

        container.append(card);
    });

    console.log('✅ displayMobileCards 완료, 카드 개수:', assets.length);
}

function getTypeIcon(type) {
    const iconMap = {
        '체크카드': 'credit_card',
        '신용카드': 'payment',
        '예금': 'account_balance',
        '적금': 'savings',
        '현금': 'payments',
        '기타': 'account_balance_wallet'
    };
    return iconMap[type] || 'account_balance_wallet';
}

function calculateTotal(assets) {
    let total = 0;
    assets.forEach(function(asset) {
        total += parseInt(asset.balance) || 0;
    });
    $('#total-amount').text(formatMoney(total));
}

function showAddForm() {
    editingAssetId = null;
    $('#form-title').text('현금자산 추가');
    clearForm();
    $('#asset-form').show();
    $('#asset-type').focus();
}

function editAsset(id) {
    editingAssetId = id;
    $('#form-title').text('현금자산 수정');

    // 현재 데이터 로드
    $.ajax({
        url: '/api/cash-assets/' + id,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                fillForm(response.data);
                $('#asset-form').show();
            } else {
                showMessage('자산 정보 로드 실패: ' + response.message, 'error');
            }
        },
        error: function() {
            showMessage('자산 정보를 불러올 수 없습니다.', 'error');
        }
    });
}

function fillForm(asset) {
    $('#asset-type').val(asset.type || '현금');
    $('#bank-name').val(asset.account_name || asset.bank_name || '');
    $('#item-name').val(asset.item_name || '');
    $('#balance').val(asset.balance || 0);
    $('#notes').val(asset.notes || '');
}

function clearForm() {
    $('#asset-type').val('현금');
    $('#bank-name').val('');
    $('#item-name').val('');
    $('#balance').val('');
    $('#notes').val('');
}

function hideForm() {
    $('#asset-form').hide();
    editingAssetId = null;
}

function saveAsset() {
    let data = {
        type: $('#asset-type').val(),
        account_name: $('#bank-name').val().trim(),
        item_name: $('#item-name').val().trim(),
        balance: parseInt($('#balance').val()) || 0,
        notes: $('#notes').val().trim()
    };

    // 유효성 검사
    if (!data.account_name || !data.item_name || data.balance < 0) {
        showMessage('필수 항목을 모두 입력해주세요.', 'error');
        return;
    }

    let url = '/api/cash-assets';
    let method = 'POST';

    if (editingAssetId) {
        url += '/' + editingAssetId;
        method = 'PUT';
    }

    $.ajax({
        url: url,
        method: method,
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            if (response.success) {
                showMessage(editingAssetId ? '자산이 수정되었습니다.' : '자산이 추가되었습니다.', 'success');
                hideForm();
                loadAssets();
            } else {
                showMessage('저장 실패: ' + response.message, 'error');
            }
        },
        error: function() {
            showMessage('서버 오류가 발생했습니다.', 'error');
        }
    });
}

function deleteAsset(id) {
    if (!confirm('이 자산을 삭제하시겠습니까?')) {
        return;
    }

    $.ajax({
        url: '/api/cash-assets/' + id,
        method: 'DELETE',
        success: function(response) {
            if (response.success) {
                showMessage('자산이 삭제되었습니다.', 'success');
                loadAssets();
            } else {
                showMessage('삭제 실패: ' + response.message, 'error');
            }
        },
        error: function() {
            showMessage('서버 오류가 발생했습니다.', 'error');
        }
    });
}

function formatMoney(amount) {
    if (amount == null) return '0원';
    return parseInt(amount).toLocaleString() + '원';
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    let date = new Date(dateStr);
    return date.getFullYear() + '-' +
           String(date.getMonth() + 1).padStart(2, '0') + '-' +
           String(date.getDate()).padStart(2, '0');
}

function showMessage(text, type) {
    let colorClass = 'blue';
    if (type === 'success') colorClass = 'green';
    else if (type === 'error') colorClass = 'red';
    else if (type === 'info') colorClass = 'blue';

    $('#message').removeClass('green red blue')
                 .addClass(colorClass)
                 .addClass('white-text');
    $('#message-text').text(text);
    $('#message').show();

    setTimeout(function() {
        $('#message').fadeOut();
    }, 3000);
}
</script>

<?php include 'includes/footer.php'; ?>