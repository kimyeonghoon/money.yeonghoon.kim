<?php
$pageTitle = '연금자산';
include 'includes/header.php';
?>

    <main class="container">
        <div class="section fade-in">
            <div class="row">
                <div class="col s12">
                    <h4 class="section-title"><i class="material-icons left">security</i>연금자산 관리</h4>
                </div>
            </div>

            <!-- 추가/수정 폼 -->
            <div id="asset-form" class="card" style="display: none;">
                <div class="card-content">
                    <span class="card-title" id="form-title">연금자산 추가</span>
                    <form>
                        <div class="row">
                            <div class="input-field col s12 m6">
                                <select id="pension-type">
                                    <option value="" disabled selected>선택하세요</option>
                                    <option value="국민연금">🏛️ 국민연금</option>
                                    <option value="개인연금">👤 개인연금</option>
                                    <option value="퇴직연금">🏢 퇴직연금</option>
                                    <option value="연금저축">💰 연금저축</option>
                                    <option value="IRP">📈 IRP</option>
                                    <option value="기타">📝 기타</option>
                                </select>
                                <label>연금유형*</label>
                            </div>
                            <div class="input-field col s12 m6">
                                <input id="item-name" type="text" class="validate" required>
                                <label for="item-name">상품명*</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12 m6">
                                <input id="contribution" type="number" class="validate" required>
                                <label for="contribution">납입원금* (원)</label>
                            </div>
                            <div class="input-field col s12 m6">
                                <input id="current-value" type="number" class="validate" required>
                                <label for="current-value">현재가치* (원)</label>
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
                        <i class="material-icons left">add</i>새 연금자산 추가
                    </button>
                    <button id="refresh-btn" class="btn grey waves-effect waves-light">
                        <i class="material-icons left">refresh</i>새로고침
                    </button>
                </div>
            </div>

            <!-- 연금자산 목록 테이블 -->
            <div class="row">
                <div class="col s12">
                    <div id="loading" class="center-align">
                        <div class="preloader-wrapper active">
                            <div class="spinner-layer spinner-purple-only">
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
                        <p>연금자산 목록을 불러오는 중...</p>
                    </div>

                    <div class="card" id="assets-table-card" style="display: none;">
                        <div class="card-content">
                            <div class="responsive-table">
                                <table class="striped">
                                    <thead>
                                        <tr>
                                            <th>연금유형</th>
                                            <th>상품명</th>
                                            <th>납입원금</th>
                                            <th>현재가치</th>
                                            <th>수익률</th>
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

                    <div id="no-data" class="card grey lighten-4" style="display: none;">
                        <div class="card-content center-align">
                            <span class="card-title grey-text">
                                <i class="material-icons large">security</i>
                            </span>
                            <p class="grey-text">등록된 연금자산이 없습니다.</p>
                            <p class="grey-text">새 자산을 추가해보세요! 👴</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 연금 현황 요약 -->
            <div class="row">
                <div class="col s12 m6 l3">
                    <div class="stats-card purple white-text">
                        <div class="stats-icon">
                            <i class="material-icons">account_balance</i>
                        </div>
                        <div class="stats-number" id="total-contribution">-</div>
                        <div class="stats-label">총 납입원금</div>
                    </div>
                </div>
                <div class="col s12 m6 l3">
                    <div class="stats-card indigo white-text">
                        <div class="stats-icon">
                            <i class="material-icons">security</i>
                        </div>
                        <div class="stats-number" id="total-current">-</div>
                        <div class="stats-label">총 현재가치</div>
                    </div>
                </div>
                <div class="col s12 m6 l3">
                    <div class="stats-card teal white-text">
                        <div class="stats-icon">
                            <i class="material-icons">assessment</i>
                        </div>
                        <div class="stats-number" id="total-return">-</div>
                        <div class="stats-label">총 수익률</div>
                    </div>
                </div>
                <div class="col s12 m6 l3">
                    <div class="stats-card green white-text">
                        <div class="stats-icon">
                            <i class="material-icons">attach_money</i>
                        </div>
                        <div class="stats-number" id="total-profit">-</div>
                        <div class="stats-label">평가손익</div>
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
    loadAssets();

    // 이벤트 핸들러
    $('#add-asset-btn').click(showAddForm);
    $('#cancel-btn').click(hideForm);
    $('#save-btn').click(saveAsset);
    $('#refresh-btn').click(loadAssets);
});

function loadAssets() {
    $('#loading').show();
    $('#assets-table-card').hide();
    $('#no-data').hide();

    $.ajax({
        url: '/api/pension-assets',
        method: 'GET',
        success: function(response) {
            $('#loading').hide();
            if (response.success) {
                if (response.data.length === 0) {
                    $('#no-data').show();
                    updateSummary([]);
                } else {
                    displayAssets(response.data);
                    updateSummary(response.data);
                    $('#assets-table-card').show();
                }
            } else {
                showMessage('데이터 로드 실패: ' + response.message, 'error');
            }
        },
        error: function() {
            $('#loading').hide();
            showMessage('서버와의 연결에 실패했습니다.', 'error');
        }
    });
}

function displayAssets(assets) {
    let tbody = $('#assets-table-body');
    tbody.empty();

    assets.forEach(function(asset) {
        // API 응답 구조에 맞게 필드명 변경
        let contribution = parseInt(asset.deposit_amount || asset.contribution) || 0;
        let currentValue = parseInt(asset.current_value) || 0;
        let returnRate = contribution > 0 ? ((currentValue - contribution) / contribution * 100).toFixed(2) : 0;
        let returnClass = returnRate >= 0 ? 'positive' : 'negative';

        let row = '<tr>' +
                  '<td>' + (asset.type || asset.pension_type || '-') + '</td>' +
                  '<td>' + (asset.item_name || '-') + '</td>' +
                  '<td style="font-weight: bold;">' + formatMoney(contribution) + '</td>' +
                  '<td style="font-weight: bold; color: #0066cc;">' + formatMoney(currentValue) + '</td>' +
                  '<td class="' + returnClass + '" style="font-weight: bold;">' + returnRate + '%</td>' +
                  '<td>' + (asset.notes || '-') + '</td>' +
                  '<td>' + formatDate(asset.updated_at || asset.created_at) + '</td>' +
                  '<td>' +
                  '<button onclick="editAsset(' + asset.id + ')" class="btn-small waves-effect waves-light blue" style="margin-right: 5px;"><i class="material-icons left">edit</i>수정</button>' +
                  '<button onclick="deleteAsset(' + asset.id + ')" class="btn-small waves-effect waves-light red"><i class="material-icons left">delete</i>삭제</button>' +
                  '</td>' +
                  '</tr>';
        tbody.append(row);
    });

}

function updateSummary(assets) {
    let totalContribution = 0;
    let totalCurrent = 0;

    assets.forEach(function(asset) {
        totalContribution += parseInt(asset.deposit_amount || asset.contribution) || 0;
        totalCurrent += parseInt(asset.current_value) || 0;
    });

    let totalReturn = totalContribution > 0 ? ((totalCurrent - totalContribution) / totalContribution * 100).toFixed(2) : 0;
    let totalProfit = totalCurrent - totalContribution;

    $('#total-contribution').text(formatMoney(totalContribution));
    $('#total-current').text(formatMoney(totalCurrent));
    $('#total-return').text(totalReturn + '%').removeClass('positive negative').addClass(totalReturn >= 0 ? 'positive' : 'negative');
    $('#total-profit').text(formatMoney(Math.abs(totalProfit))).removeClass('positive negative').addClass(totalProfit >= 0 ? 'positive' : 'negative');
}

function showAddForm() {
    editingAssetId = null;
    $('#form-title').text('연금자산 추가');
    clearForm();
    $('#asset-form').show();
    $('#pension-type').focus();
}

function editAsset(id) {
    editingAssetId = id;
    $('#form-title').text('연금자산 수정');

    $.ajax({
        url: '/api/pension-assets/' + id,
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
    $('#pension-type').val(asset.type || asset.pension_type || '개인연금');
    $('#item-name').val(asset.item_name || '');
    $('#contribution').val(asset.deposit_amount || asset.contribution || 0);
    $('#current-value').val(asset.current_value || 0);
    $('#notes').val(asset.notes || '');
}

function clearForm() {
    $('#pension-type').val('개인연금');
    $('#item-name').val('');
    $('#contribution').val('');
    $('#current-value').val('');
    $('#notes').val('');
}

function hideForm() {
    $('#asset-form').hide();
    editingAssetId = null;
}

function saveAsset() {
    let data = {
        type: $('#pension-type').val(), // API가 기대하는 필드명으로 변경
        item_name: $('#item-name').val().trim(),
        deposit_amount: parseInt($('#contribution').val()) || 0, // contribution -> deposit_amount
        current_value: parseInt($('#current-value').val()) || 0,
        notes: $('#notes').val().trim()
    };

    // 유효성 검사
    if (!data.item_name || data.deposit_amount < 0 || data.current_value < 0) {
        showMessage('필수 항목을 모두 입력해주세요.', 'error');
        return;
    }

    let url = '/api/pension-assets';
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
                showMessage(editingAssetId ? '연금자산이 수정되었습니다.' : '연금자산이 추가되었습니다.', 'success');
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
    if (!confirm('이 연금자산을 삭제하시겠습니까?')) {
        return;
    }

    $.ajax({
        url: '/api/pension-assets/' + id,
        method: 'DELETE',
        success: function(response) {
            if (response.success) {
                showMessage('연금자산이 삭제되었습니다.', 'success');
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