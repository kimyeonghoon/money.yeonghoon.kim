<?php
$pageTitle = '대시보드';
include 'includes/header.php';
?>

<!-- Inline editing styles -->
<style>
    .balance-cell.editable:hover {
        background-color: #e3f2fd !important;
        border-radius: 4px;
        transition: background-color 0.2s ease;
    }

    .balance-cell.editable::after {
        content: "✏️";
        font-size: 12px;
        margin-left: 5px;
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .balance-cell.editable:hover::after {
        opacity: 0.7;
    }

    .balance-input {
        font-family: 'Roboto', sans-serif !important;
    }

    .editing-hint {
        font-size: 11px;
        color: #666;
        text-align: center;
        margin-top: 10px;
        font-style: italic;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .section-header .section-title {
        margin: 0;
    }

    .asset-row {
        transition: background-color 0.2s ease;
        user-select: none;
    }

    .asset-row:hover {
        background-color: #f8f9fa !important;
    }

    .asset-row.long-press-active {
        background-color: #e3f2fd !important;
        animation: pulse 0.5s ease-in-out;
    }

    /* 드래그 앤 드롭 스타일 */
    .sortable-enabled .asset-row {
        cursor: move;
        cursor: grab;
    }

    .sortable-enabled .asset-row:active {
        cursor: grabbing;
    }

    .ui-sortable-helper {
        background-color: #fff3e0 !important;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        transform: rotate(2deg);
    }

    .ui-sortable-placeholder {
        background-color: #f5f5f5 !important;
        border: 2px dashed #ccc !important;
        height: 50px;
        visibility: visible !important;
    }

    .drag-handle {
        color: #999;
        cursor: grab;
        padding: 5px;
        display: inline-block;
        opacity: 0.7;
        transition: opacity 0.3s;
    }

    .sortable-enabled .drag-handle {
        opacity: 1;
    }

    .drag-handle:hover {
        color: #666;
        opacity: 1;
    }

    .reorder-toggle {
        margin-left: 10px;
    }

    /* 반응형 디스플레이 */
    @media (max-width: 768px) {
        .desktop-only {
            display: none !important;
        }
    }

    @media (min-width: 769px) {
        .mobile-only {
            display: none !important;
        }
    }

    /* 모바일 카드 스타일 */
    .asset-card {
        background: #fff;
        border-radius: 8px;
        margin-bottom: 12px;
        padding: 16px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-left: 4px solid #2196F3;
        transition: all 0.3s ease;
        user-select: none;
    }

    .asset-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        transform: translateY(-1px);
    }

    .asset-card.long-press-active {
        background-color: #e3f2fd !important;
        transform: scale(1.02);
        box-shadow: 0 6px 12px rgba(0,0,0,0.2);
    }

    .sortable-enabled .asset-card {
        cursor: move;
        cursor: grab;
    }

    .sortable-enabled .asset-card:active {
        cursor: grabbing;
    }

    .asset-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        border-bottom: 1px solid #e0e0e0;
        padding-bottom: 8px;
    }

    .asset-card-title {
        font-weight: bold;
        font-size: 1.1em;
        color: #2196F3;
    }

    .asset-card-type {
        background: #f5f5f5;
        color: #666;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.85em;
    }

    .asset-card-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .asset-card-row:last-child {
        margin-bottom: 0;
    }

    .asset-card-label {
        color: #666;
        font-size: 0.9em;
        min-width: 60px;
    }

    .asset-card-value {
        color: #333;
        font-weight: 500;
        text-align: right;
        flex: 1;
    }

    .asset-card-balance {
        font-weight: bold;
        color: #4CAF50;
        font-size: 1.1em;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
        transition: background-color 0.3s;
    }

    .asset-card-balance:hover {
        background-color: #f0f8f0;
    }

    .asset-card-percentage {
        color: #666;
        font-size: 0.9em;
    }

    .mobile-drag-handle {
        color: #999;
        opacity: 0.7;
        transition: opacity 0.3s;
        display: none;
    }

    .sortable-enabled .mobile-drag-handle {
        display: block;
        opacity: 1;
    }

    @keyframes pulse {
        0% { background-color: #e3f2fd; }
        50% { background-color: #bbdefb; }
        100% { background-color: #e3f2fd; }
    }

    .row-hint {
        font-size: 10px;
        color: #999;
        text-align: center;
        margin-top: 5px;
        font-style: italic;
    }
</style>

    <main class="container">
        <div class="section">
            <div class="row">
                <div class="col s12">
                    <h4 class="section-title"><i class="material-icons left">dashboard</i>자산관리 대시보드</h4>
                </div>
            </div>

            <!-- 로딩 -->
            <div id="loading" class="row">
                <div class="col s12 center-align">
                    <div class="preloader-wrapper big active">
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
                    <p>데이터를 불러오는 중입니다...</p>
                </div>
            </div>

            <!-- 대시보드 컨텐츠 -->
            <div id="dashboard-content" style="display: none;">
                <!-- 현금성 자산 상세 -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h5 class="section-title">💵 현금성 자산 현황</h5>
                        <div>
                            <button id="reorder-toggle" class="btn-small waves-effect waves-light blue reorder-toggle" title="순서 변경">
                                <i class="material-icons left">swap_vert</i>순서변경
                            </button>
                            <button class="btn-floating waves-effect waves-light green modal-trigger"
                                    data-target="add-asset-modal" title="자산 추가">
                                <i class="material-icons">add</i>
                            </button>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-content">
                            <!-- 데스크톱용 테이블 -->
                            <div class="responsive-table desktop-only">
                                <table class="striped">
                                    <thead>
                                        <tr>
                                            <th>구분</th>
                                            <th>계좌</th>
                                            <th>종목명</th>
                                            <th>잔액</th>
                                            <th>비중</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cash-assets-detail-table">
                                        <tr>
                                            <td colspan="5" class="center-align">데이터를 불러오는 중...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- 모바일용 카드 -->
                            <div class="mobile-only" id="cash-assets-detail-cards">
                                <div class="center-align">데이터를 불러오는 중...</div>
                            </div>
                        </div>
                        <div class="editing-hint">
                            💡 잔액 클릭: 금액만 수정 | <span class="desktop-only">행 더블클릭</span><span class="mobile-only">카드 길게 누르기</span>: 전체 수정
                        </div>
                    </div>
                </div>
            </div>

            <!-- 에러 메시지 -->
            <div id="error-message" class="card red white-text" style="display: none;">
                <div class="card-content">
                    <span class="card-title">오류 발생</span>
                    <p>데이터를 불러오는 중 오류가 발생했습니다. 새로고침 후 다시 시도해주세요.</p>
                </div>
            </div>
        </div>
    </main>

    <!-- 자산 수정 모달 -->
    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <h4><i class="material-icons left">edit</i>자산 정보 수정</h4>
            <div class="row">
                <form id="edit-form" class="col s12">
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="edit-account" type="text" maxlength="100">
                            <label for="edit-account">계좌명</label>
                            <span class="helper-text">예: OK저축은행, 집, 카카오뱅크 등</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="edit-item-name" type="text" maxlength="200" required>
                            <label for="edit-item-name">종목명 *</label>
                            <span class="helper-text">예: 짠테크통장, 금고, 적금 등</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-close waves-effect waves-light btn-flat">취소</button>
            <button id="save-edit" class="waves-effect waves-light btn blue">
                <i class="material-icons left">save</i>저장
            </button>
        </div>
    </div>

    <!-- 자산 추가 모달 -->
    <div id="add-asset-modal" class="modal modal-fixed-footer">
        <div class="modal-content">
            <h4><i class="material-icons left">add</i>현금성 자산 추가</h4>
            <div class="row">
                <form id="add-form" class="col s12">
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="add-account" type="text" maxlength="100">
                            <label for="add-account">계좌명</label>
                            <span class="helper-text">예: OK저축은행, 집, 카카오뱅크 등</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="add-item-name" type="text" maxlength="200" required>
                            <label for="add-item-name">종목명 *</label>
                            <span class="helper-text">예: 짠테크통장, 금고, 적금 등</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="add-balance" type="number" min="0" step="1000" value="0" required>
                            <label for="add-balance">초기 잔액 *</label>
                            <span class="helper-text">단위: 원</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-close waves-effect waves-light btn-flat">취소</button>
            <button id="save-add" class="waves-effect waves-light btn green">
                <i class="material-icons left">add</i>추가
            </button>
        </div>
    </div>

<script>
$(document).ready(function() {
    // 모달 초기화
    M.Modal.init(document.getElementById('edit-modal'));
    M.Modal.init(document.getElementById('add-asset-modal'));

    // 저장 버튼 이벤트 핸들러
    $('#save-edit').on('click', function() {
        saveEditedAsset();
    });

    // 추가 버튼 이벤트 핸들러
    $('#save-add').on('click', function() {
        saveNewAsset();
    });

    // 순서 변경 토글 버튼 이벤트 핸들러
    $('#reorder-toggle').on('click', function() {
        toggleReorderMode();
    });

    loadCashAssets();
});

function loadCashAssets() {
    $.ajax({
        url: 'http://localhost:8080/api/cash-assets',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                updateCashAssetsTable(response.data.data);
                $('#loading').hide();
                $('#dashboard-content').show();
            } else {
                showError('현금 자산 데이터 로드 실패: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            showError('서버와의 연결에 실패했습니다: ' + error);
        }
    });
}

function updateCashAssetsTable(assets) {
    let tbody = $('#cash-assets-detail-table');
    let cardsContainer = $('#cash-assets-detail-cards');

    tbody.empty();
    cardsContainer.empty();

    let totalBalance = 0;

    if (assets.length === 0) {
        tbody.append('<tr><td colspan="5" class="center-align">현금성 자산이 없습니다.</td></tr>');
        cardsContainer.append('<div class="center-align">현금성 자산이 없습니다.</div>');
        return;
    }

    // 자산 목록 표시 (테이블과 카드 모두)
    assets.forEach(function(asset) {
        totalBalance += parseInt(asset.balance || 0);

        // 테이블 행 생성 (데스크톱용)
        let $row = $('<tr class="asset-row" data-asset-id="' + asset.id + '" ' +
                     'data-type="' + (asset.type || '현금') + '" ' +
                     'data-account="' + (asset.account_name || '') + '" ' +
                     'data-item-name="' + (asset.item_name || '') + '" ' +
                     'data-balance="' + asset.balance + '">' +
                     '<td style="color: #424242 !important;">' +
                         '<span class="drag-handle" style="display: none;"><i class="material-icons">drag_handle</i></span>' +
                         (asset.type || '현금') +
                     '</td>' +
                     '<td style="color: #424242 !important;">' + (asset.account_name || '-') + '</td>' +
                     '<td style="color: #424242 !important;">' + (asset.item_name || '-') + '</td>' +
                     '<td class="positive balance-cell editable" style="font-weight: bold; cursor: pointer;" ' +
                         'data-asset-id="' + asset.id + '" data-original-balance="' + asset.balance + '">' +
                         '₩' + parseInt(asset.balance || 0).toLocaleString() +
                     '</td>' +
                     '<td style="color: #424242 !important;">' + (asset.percentage || 0) + '%</td>' +
                     '</tr>');
        tbody.append($row);

        // 카드 생성 (모바일용)
        let $card = $('<div class="asset-card" data-asset-id="' + asset.id + '" ' +
                      'data-type="' + (asset.type || '현금') + '" ' +
                      'data-account="' + (asset.account_name || '') + '" ' +
                      'data-item-name="' + (asset.item_name || '') + '" ' +
                      'data-balance="' + asset.balance + '">' +
                      '<div class="asset-card-header">' +
                          '<div class="asset-card-title">' + (asset.item_name || '-') + '</div>' +
                          '<div class="asset-card-type">' + (asset.type || '현금') + '</div>' +
                          '<div class="mobile-drag-handle"><i class="material-icons">drag_handle</i></div>' +
                      '</div>' +
                      '<div class="asset-card-row">' +
                          '<div class="asset-card-label">계좌</div>' +
                          '<div class="asset-card-value">' + (asset.account_name || '-') + '</div>' +
                      '</div>' +
                      '<div class="asset-card-row">' +
                          '<div class="asset-card-label">잔액</div>' +
                          '<div class="asset-card-balance balance-cell editable" ' +
                              'data-asset-id="' + asset.id + '" data-original-balance="' + asset.balance + '">' +
                              '₩' + parseInt(asset.balance || 0).toLocaleString() +
                          '</div>' +
                      '</div>' +
                      '<div class="asset-card-row">' +
                          '<div class="asset-card-label">비중</div>' +
                          '<div class="asset-card-percentage">' + (asset.percentage || 0) + '%</div>' +
                      '</div>' +
                      '</div>');
        cardsContainer.append($card);
    });

    // 잔액 편집 이벤트 리스너 추가
    setupBalanceEditing();

    // 더블클릭/롱프레스 이벤트 리스너 추가
    setupRowEditing();

    // 총합 행 추가 (테이블만)
    let totalRow = '<tr style="background-color: #f5f5f5; font-weight: bold;">' +
                   '<td colspan="3" style="color: #424242 !important; text-align: right;">총 현금성 자산:</td>' +
                   '<td class="positive" style="font-weight: bold;">₩' + totalBalance.toLocaleString() + '</td>' +
                   '<td style="color: #424242 !important;">100%</td>' +
                   '</tr>';
    tbody.append(totalRow);

    // 총합 카드 추가 (모바일만)
    let totalCard = '<div class="asset-card" style="border-left-color: #4CAF50; background-color: #f8f9fa;">' +
                    '<div class="asset-card-header">' +
                        '<div class="asset-card-title" style="color: #4CAF50;">총 현금성 자산</div>' +
                    '</div>' +
                    '<div class="asset-card-row">' +
                        '<div class="asset-card-label">총 잔액</div>' +
                        '<div style="font-weight: bold; color: #4CAF50; font-size: 1.2em;">₩' + totalBalance.toLocaleString() + '</div>' +
                    '</div>' +
                    '<div class="asset-card-row">' +
                        '<div class="asset-card-label">비중</div>' +
                        '<div class="asset-card-percentage" style="font-weight: bold;">100%</div>' +
                    '</div>' +
                    '</div>';
    cardsContainer.append(totalCard);
}

function setupBalanceEditing() {
    let currentlyEditing = null;

    // 잔액 셀 클릭 이벤트
    $('.balance-cell.editable').off('click').on('click', function() {
        if (currentlyEditing && currentlyEditing[0] !== this) {
            // 다른 셀이 편집중이면 먼저 처리
            handleEditCancel(currentlyEditing);
        }

        if ($(this).find('input').length > 0) {
            return; // 이미 편집중이면 무시
        }

        startBalanceEdit($(this));
        currentlyEditing = $(this);
    });

    // 다른 곳 클릭 시 편집 완료 확인
    $(document).off('click.balance-edit').on('click.balance-edit', function(e) {
        if (currentlyEditing && !$(e.target).closest('.balance-cell').length) {
            handleEditComplete(currentlyEditing);
            currentlyEditing = null;
        }
    });
}

function startBalanceEdit(cell) {
    let originalBalance = parseInt(cell.data('original-balance'));
    let assetId = cell.data('asset-id');

    // 현재 내용을 input으로 교체
    let input = $('<input type="number" class="balance-input" value="' + originalBalance + '" ' +
                 'style="width: 100%; border: 2px solid #2196F3; padding: 5px; text-align: right; font-weight: bold;" ' +
                 'min="0" step="1000">');

    cell.html(input);
    input.focus().select();

    // Enter 키로 확인
    input.on('keydown', function(e) {
        if (e.key === 'Enter') {
            handleEditComplete(cell);
            $(document).off('click.balance-edit');
        } else if (e.key === 'Escape') {
            handleEditCancel(cell);
            $(document).off('click.balance-edit');
        }
    });
}

function handleEditComplete(cell) {
    let input = cell.find('.balance-input');
    if (input.length === 0) return;

    let newBalance = parseInt(input.val()) || 0;
    let originalBalance = parseInt(cell.data('original-balance'));
    let assetId = cell.data('asset-id');

    if (newBalance !== originalBalance) {
        // 변경사항이 있으면 확인
        if (confirm('잔액을 ₩' + newBalance.toLocaleString() + '(으)로 수정하시겠습니까?')) {
            updateAssetBalance(assetId, newBalance, cell);
        } else {
            // 취소 시 원래 값으로 복원
            restoreOriginalBalance(cell);
        }
    } else {
        // 변경사항 없으면 그냥 복원
        restoreOriginalBalance(cell);
    }
}

function handleEditCancel(cell) {
    restoreOriginalBalance(cell);
}

function restoreOriginalBalance(cell) {
    let originalBalance = parseInt(cell.data('original-balance'));
    cell.html('₩' + originalBalance.toLocaleString());
}

function updateAssetBalance(assetId, newBalance, cell) {
    // 로딩 표시
    cell.html('<i class="material-icons" style="font-size: 18px;">hourglass_empty</i> 수정중...');

    $.ajax({
        url: 'http://localhost:8080/api/cash-assets/' + assetId,
        method: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify({
            balance: newBalance
        }),
        success: function(response) {
            if (response.success) {
                // 성공 시 새로운 값으로 업데이트
                cell.data('original-balance', newBalance);
                cell.html('₩' + newBalance.toLocaleString());

                // 성공 메시지 (짧게 표시)
                showSuccessMessage('잔액이 수정되었습니다.');

                // 전체 테이블 새로고침 (비중 재계산을 위해)
                setTimeout(function() {
                    loadCashAssets();
                }, 500);
            } else {
                showError('수정 실패: ' + response.message);
                restoreOriginalBalance(cell);
            }
        },
        error: function(xhr, status, error) {
            showError('수정 중 오류 발생: ' + error);
            restoreOriginalBalance(cell);
        }
    });
}

function showSuccessMessage(message) {
    // 임시 성공 메시지 표시
    let successAlert = $('<div class="card green white-text" style="position: fixed; top: 20px; right: 20px; z-index: 1000; padding: 10px;">' +
                        '<div class="card-content">' + message + '</div></div>');
    $('body').append(successAlert);

    setTimeout(function() {
        successAlert.fadeOut(500, function() {
            $(this).remove();
        });
    }, 2000);
}

function setupRowEditing() {
    let longPressTimer;
    let isLongPress = false;

    // 테이블 행과 카드 모두에서 이벤트 제거
    $('.asset-row, .asset-card').off('dblclick touchstart touchend touchmove');

    // 데스크톱: 더블클릭 이벤트 (테이블 행)
    $('.asset-row').on('dblclick', function(e) {
        // 잔액 셀은 제외 (인라인 편집 우선)
        if (!$(e.target).hasClass('balance-cell')) {
            e.preventDefault();
            openEditModal($(this));
        }
    });

    // 모바일: 카드 롱프레스 이벤트
    $('.asset-card').on('touchstart', function(e) {
        if ($(e.target).hasClass('balance-cell')) return; // 잔액 셀 제외

        const $card = $(this);
        isLongPress = false;

        // 햅틱 피드백 대신 시각적 피드백
        longPressTimer = setTimeout(function() {
            isLongPress = true;
            $card.addClass('long-press-active');

            // 진동 효과 (지원되는 경우)
            if ('vibrate' in navigator) {
                navigator.vibrate(100);
            }

            openEditModal($card);

            setTimeout(function() {
                $card.removeClass('long-press-active');
            }, 300);
        }, 600); // 600ms 롱프레스
    });

    // 카드 터치 이벤트 정리
    $('.asset-card').on('touchend touchmove', function() {
        clearTimeout(longPressTimer);
    });

    // 일반 터치는 롱프레스가 아닐 때만 처리
    $('.asset-card').on('touchend', function() {
        if (!isLongPress) {
            // 일반 터치 처리 (필요시)
        }
    });
}

function openEditModal($row) {
    const assetId = $row.data('asset-id');
    const account = $row.data('account');
    const itemName = $row.data('item-name');

    // 모달 폼에 데이터 채우기
    $('#edit-account').val(account);
    $('#edit-item-name').val(itemName);

    // 모달에 자산 ID 저장
    $('#edit-modal').data('asset-id', assetId);

    // Materialize 컴포넌트 업데이트
    M.updateTextFields();

    // 모달 열기
    const modal = M.Modal.getInstance(document.getElementById('edit-modal'));
    modal.open();
}

function saveEditedAsset() {
    const assetId = $('#edit-modal').data('asset-id');
    const formData = {
        type: '현금',
        account_name: $('#edit-account').val(),
        item_name: $('#edit-item-name').val()
    };

    // 간단한 클라이언트 검증
    if (!formData.item_name.trim()) {
        M.toast({html: '종목명을 입력해주세요.', classes: 'red'});
        return;
    }

    // API 호출
    $.ajax({
        url: 'http://localhost:8080/api/cash-assets/' + assetId,
        method: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function(response) {
            if (response.success) {
                // 모달 닫기
                const modal = M.Modal.getInstance(document.getElementById('edit-modal'));
                modal.close();

                // 성공 메시지
                M.toast({html: '자산 정보가 수정되었습니다.', classes: 'green'});

                // 테이블 새로고침
                setTimeout(function() {
                    loadCashAssets();
                }, 500);
            } else {
                M.toast({html: '수정 실패: ' + response.message, classes: 'red'});
            }
        },
        error: function(xhr, status, error) {
            M.toast({html: '수정 중 오류 발생: ' + error, classes: 'red'});
        }
    });
}

function saveNewAsset() {
    const formData = {
        type: '현금',
        account_name: $('#add-account').val(),
        item_name: $('#add-item-name').val(),
        balance: parseInt($('#add-balance').val()) || 0
    };

    // 간단한 클라이언트 검증
    if (!formData.item_name.trim()) {
        M.toast({html: '종목명을 입력해주세요.', classes: 'red'});
        return;
    }

    // API 호출
    $.ajax({
        url: 'http://localhost:8080/api/cash-assets',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function(response) {
            if (response.success) {
                // 모달 닫기
                const modal = M.Modal.getInstance(document.getElementById('add-asset-modal'));
                modal.close();

                // 폼 초기화
                $('#add-form')[0].reset();
                M.updateTextFields();

                // 성공 메시지
                M.toast({html: '새 자산이 추가되었습니다.', classes: 'green'});

                // 테이블 새로고침
                setTimeout(function() {
                    loadCashAssets();
                }, 500);
            } else {
                M.toast({html: '추가 실패: ' + response.message, classes: 'red'});
            }
        },
        error: function(xhr, status, error) {
            M.toast({html: '추가 중 오류 발생: ' + error, classes: 'red'});
        }
    });
}

let isReorderMode = false;

function toggleReorderMode() {
    isReorderMode = !isReorderMode;
    const $toggle = $('#reorder-toggle');
    const $tbody = $('#cash-assets-detail-table');
    const $cardsContainer = $('#cash-assets-detail-cards');

    if (isReorderMode) {
        // 순서 변경 모드 활성화
        $toggle.removeClass('blue').addClass('orange').html('<i class="material-icons left">check</i>완료');

        // 테이블과 카드 모두에 sortable-enabled 클래스 추가
        $tbody.addClass('sortable-enabled');
        $cardsContainer.addClass('sortable-enabled');

        // 드래그 핸들 표시
        $('.drag-handle, .mobile-drag-handle').show();

        // 테이블 sortable 활성화 (데스크톱)
        $tbody.sortable({
            handle: '.drag-handle',
            helper: 'clone',
            placeholder: 'ui-sortable-placeholder',
            start: function(e, ui) {
                ui.placeholder.height(ui.item.height());
            },
            stop: function(e, ui) {
                saveNewOrder();
            }
        });

        // 카드 sortable 활성화 (모바일)
        $cardsContainer.sortable({
            handle: '.mobile-drag-handle',
            helper: 'clone',
            placeholder: 'ui-sortable-placeholder',
            start: function(e, ui) {
                ui.placeholder.height(ui.item.height());
            },
            stop: function(e, ui) {
                saveNewOrder();
            }
        });

        // 편집 기능 비활성화
        $('.balance-cell.editable').removeClass('editable').addClass('disabled-while-sorting');

        M.toast({html: '드래그하여 순서를 변경하세요', classes: 'blue'});
    } else {
        // 일반 모드로 복원
        $toggle.removeClass('orange').addClass('blue').html('<i class="material-icons left">swap_vert</i>순서변경');

        // sortable-enabled 클래스 제거
        $tbody.removeClass('sortable-enabled');
        $cardsContainer.removeClass('sortable-enabled');

        // 드래그 핸들 숨기기
        $('.drag-handle, .mobile-drag-handle').hide();

        // jQuery UI sortable 비활성화
        if ($tbody.hasClass('ui-sortable')) {
            $tbody.sortable('destroy');
        }
        if ($cardsContainer.hasClass('ui-sortable')) {
            $cardsContainer.sortable('destroy');
        }

        // 편집 기능 복원
        $('.disabled-while-sorting').addClass('editable').removeClass('disabled-while-sorting');

        M.toast({html: '순서 변경이 완료되었습니다', classes: 'green'});
    }
}

function saveNewOrder() {
    const orders = [];

    // 현재 보이는 컨테이너(데스크톱: 테이블, 모바일: 카드)에서 순서 가져오기
    if ($(window).width() > 768) {
        // 데스크톱: 테이블 행에서 순서 가져오기
        $('#cash-assets-detail-table .asset-row').each(function(index) {
            const assetId = $(this).data('asset-id');
            if (assetId) {
                orders.push({
                    id: parseInt(assetId)
                });
            }
        });
    } else {
        // 모바일: 카드에서 순서 가져오기
        $('#cash-assets-detail-cards .asset-card').each(function(index) {
            const assetId = $(this).data('asset-id');
            if (assetId) {
                orders.push({
                    id: parseInt(assetId)
                });
            }
        });
    }

    // API 호출하여 순서 저장
    $.ajax({
        url: 'http://localhost:8080/api/cash-assets/reorder',
        method: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify({
            orders: orders
        }),
        success: function(response) {
            if (response.success) {
                // 순서가 성공적으로 저장됨
                console.log('Order updated successfully');
            } else {
                M.toast({html: '순서 저장 실패: ' + response.message, classes: 'red'});
                // 실패시 테이블 새로고침
                loadCashAssets();
            }
        },
        error: function(xhr, status, error) {
            M.toast({html: '순서 저장 중 오류 발생: ' + error, classes: 'red'});
            // 실패시 테이블 새로고침
            loadCashAssets();
        }
    });
}

function showError(message) {
    $('#loading').hide();
    $('#error-message .card-content span.card-title').text('오류 발생');
    $('#error-message .card-content p').text(message);
    $('#error-message').show();
}
</script>

<?php include 'includes/footer.php'; ?>