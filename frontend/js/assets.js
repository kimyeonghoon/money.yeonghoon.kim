// API Base URL (프로덕션: /api, 개발: ' + API_BASE_URL + ')
const API_BASE_URL = window.location.hostname === 'localhost' ? 'http://localhost:8080/api' : '/api';

/**
 * 자산현황 페이지 JavaScript - 머니매니저 시스템
 *
 * 자산 관리 메인 페이지의 모든 인터랙티브 기능을 담당하는 핵심 스크립트입니다.
 * 현금, 투자, 연금 자산의 CRUD 작업과 UI 상호작용을 처리합니다.
 *
 * 주요 기능:
 * - 자산 데이터 로드 및 표시 (실시간/아카이브)
 * - 자산 추가/수정/삭제 (모달 기반 CRUD)
 * - 인라인 편집 (잔액 직접 수정)
 * - 드래그 앤 드롭 순서 변경
 * - 모바일 친화적 카드 레이아웃
 * - 아카이브 모드 전환
 * - 실시간 총액 및 비율 계산
 * - 에러 처리 및 사용자 피드백
 *
 * 아키텍처:
 * - RESTful API 통신 (fetch API 사용)
 * - 반응형 UI (모바일/데스크톱 대응)
 * - 상태 관리 (전역 변수 및 로컬 스토리지)
 * - 이벤트 기반 프로그래밍
 *
 * 의존성:
 * - jQuery 3.6.0+
 * - Materialize CSS 1.0.0+
 * - SortableJS (드래그 앤 드롭)
 * - 백엔드 API 서버
 *
 * @package MoneyManager
 * @version 1.0
 * @author YeongHoon Kim
 */

// DOM 준비 완료시 실행
$(document).ready(function() {

    /* ================================
       Materialize CSS 컴포넌트 초기화
       ================================ */

    // 모달 컴포넌트 초기화
    M.Modal.init(document.getElementById('edit-modal'));              // 현금자산 편집 모달
    M.Modal.init(document.getElementById('add-asset-modal'));         // 현금자산 추가 모달
    M.Modal.init(document.getElementById('add-investment-modal'));    // 투자자산 추가 모달
    M.Modal.init(document.getElementById('add-pension-modal'));       // 연금자산 추가 모달
    M.Modal.init(document.getElementById('edit-pension-modal'));      // 연금자산 편집 모달
    M.Modal.init(document.getElementById('edit-investment-modal'));   // 투자자산 편집 모달

    // 셀렉트 박스 컴포넌트 초기화
    M.FormSelect.init(document.getElementById('add-investment-type'));  // 투자자산 타입 선택
    M.FormSelect.init(document.getElementById('add-pension-type'));     // 연금자산 타입 선택
    M.FormSelect.init(document.getElementById('edit-pension-type'));    // 연금자산 편집시 타입 선택
    M.FormSelect.init(document.getElementById('edit-investment-type')); // 투자자산 편집시 타입 선택

    // 저장 버튼 이벤트 핸들러
    $('#save-edit').on('click', function() {
        saveEditedAsset();
    });

    // 추가 버튼 이벤트 핸들러
    $('#save-add').on('click', function() {
        saveNewAsset();
    });

    // 투자자산 추가 버튼 이벤트 핸들러
    $('#save-investment-add').on('click', function() {
        console.log('[DEBUG] 투자자산 추가 버튼 클릭됨');
        saveNewInvestmentAsset();
    });

    // 연금자산 추가 버튼 이벤트 핸들러
    $('#save-pension-add').on('click', function() {
        console.log('[DEBUG] 연금자산 추가 버튼 클릭됨');
        saveNewPensionAsset();
    });

    // 연금자산 편집 저장 버튼 이벤트 핸들러
    $('#save-pension-edit').on('click', function() {
        saveEditedPensionAsset();
    });

    // 투자자산 편집 저장 버튼 이벤트 핸들러
    $('#save-investment-edit').on('click', function() {
        console.log('[DEBUG] 투자자산 편집 저장 버튼 클릭됨');
        saveEditedInvestmentAsset();
    });

    // 자산 삭제 버튼 이벤트 핸들러
    $('#delete-asset').on('click', function() {
        deleteAsset();
    });

    // 연금자산 삭제 버튼 이벤트 핸들러
    $('#delete-pension-asset').on('click', function() {
        deletePensionAsset();
    });

    // 투자자산 삭제 버튼 이벤트 핸들러
    $('#delete-investment-asset').on('click', function() {
        console.log('[DEBUG] 투자자산 삭제 버튼 클릭됨');
        deleteInvestmentAsset();
    });

    // 순서 변경 토글 버튼 이벤트 핸들러
    $('#reorder-toggle').on('click', function() {
        toggleReorderMode();
    });

    // 투자자산 순서 변경 토글 버튼 이벤트 핸들러
    $('#investment-reorder-toggle').on('click', function() {
        toggleInvestmentReorderMode();
    });

    // 연금자산 순서 변경 토글 버튼 이벤트 핸들러
    $('#pension-reorder-toggle').on('click', function() {
        togglePensionReorderMode();
    });

    // 모달 트리거 디버깅
    $('[data-target="add-investment-modal"]').on('click', function() {
        console.log('[DEBUG] 투자자산 추가 모달 트리거 클릭됨');
    });

    $('[data-target="add-pension-modal"]').on('click', function() {
        console.log('[DEBUG] 연금자산 추가 모달 트리거 클릭됨');
    });

    loadCashAssets();
    loadInvestmentAssets();
    loadPensionAssets();
});

function loadCashAssets() {
    $.ajax({
        url: '' + API_BASE_URL + '/cash-assets',
        method: 'GET',
        xhrFields: {
            withCredentials: true
        },
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

function loadInvestmentAssets() {
    console.log('[DEBUG] loadInvestmentAssets() 호출됨');
    $.ajax({
        url: '' + API_BASE_URL + '/investment-assets',
        method: 'GET',
        xhrFields: {
            withCredentials: true
        },
        success: function(response) {
            console.log('[DEBUG] 투자자산 로드 API 응답:', response);
            if (response.success) {
                console.log('[DEBUG] API 응답 구조 분석:');
                console.log('[DEBUG] response.data:', response.data);
                console.log('[DEBUG] response.data.data:', response.data.data);

                let data;
                if (response.data && Array.isArray(response.data.data)) {
                    data = response.data.data;
                    console.log('[DEBUG] response.data.data 사용');
                } else if (response.data && Array.isArray(response.data)) {
                    data = response.data;
                    console.log('[DEBUG] response.data 사용');
                } else if (Array.isArray(response)) {
                    data = response;
                    console.log('[DEBUG] response 직접 사용');
                } else {
                    data = [];
                    console.log('[DEBUG] 데이터 구조를 파악할 수 없어 빈 배열 사용');
                }

                console.log('[DEBUG] 최종 추출된 투자자산 데이터:', data);
                console.log('[DEBUG] 데이터 타입:', typeof data, '배열 여부:', Array.isArray(data));
                console.log('[DEBUG] updateInvestmentAssetsTable 호출 시작');
                updateInvestmentAssetsTable(data);
                console.log('[DEBUG] updateInvestmentAssetsTable 호출 완료');
                setupBalanceEditing(); // 투자자산 balance 인라인 편집 활성화
            } else {
                console.error('투자 자산 데이터 로드 실패: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('[DEBUG] 투자 자산 서버 연결 실패: ' + error);
        }
    });
}

function loadPensionAssets() {
    console.log('[DEBUG] 연금자산 로딩 함수 호출됨');
    $.ajax({
        url: '' + API_BASE_URL + '/pension-assets',
        method: 'GET',
        xhrFields: {
            withCredentials: true
        },
        success: function(response) {
            console.log('[DEBUG] 연금자산 API 응답:', response);
            if (response.success) {
                console.log('[DEBUG] 연금자산 데이터:', response.data);
                updatePensionAssetsTable(response.data);
            } else {
                console.error('연금자산 데이터 로드 실패: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('[DEBUG] 연금자산 서버 연결 실패:', error);
            console.error('[DEBUG] XHR 상태:', xhr.status);
            console.error('[DEBUG] 응답 텍스트:', xhr.responseText);
        }
    });
}

function updatePensionAssetsTable(assets) {
    let tbody = $('#pension-assets-detail-table');
    let cardsContainer = $('#pension-assets-detail-cards');

    tbody.empty();
    cardsContainer.empty();

    let totalCurrentValue = 0;
    let totalDepositAmount = 0;

    // 아카이브 모드 확인
    const isArchiveMode = (typeof ArchiveManager !== 'undefined' && ArchiveManager.isArchiveMode());
    const editableClass = isArchiveMode ? '' : 'editable';

    if (!assets || assets.length === 0) {
        tbody.append('<tr><td colspan="6" class="center-align">연금자산이 없습니다.</td></tr>');
        cardsContainer.append('<div class="center-align">연금자산이 없습니다.</div>');
        return;
    }

    // 자산 목록 표시 (테이블과 카드 모두)
    assets.forEach(function(asset) {
        let currentValue = parseInt(asset.current_value || 0);
        let depositAmount = parseInt(asset.deposit_amount || 0);
        let returnRate = depositAmount > 0 ? ((currentValue - depositAmount) / depositAmount * 100).toFixed(2) : '0.00';
        let returnClass = parseFloat(returnRate) >= 0 ? 'positive' : 'negative';

        totalCurrentValue += currentValue;
        totalDepositAmount += depositAmount;

        // 테이블 행 생성 (데스크톱용)
        let $row = $('<tr class="asset-row" data-asset-id="' + asset.id + '" ' +
                     'data-type="' + (asset.type || '연금저축') + '" ' +
                     'data-account="' + (asset.account_name || '') + '" ' +
                     'data-item-name="' + (asset.item_name || '') + '" ' +
                     'data-current-value="' + currentValue + '" ' +
                     'data-deposit-amount="' + depositAmount + '">' +
                     '<td style="color: #424242 !important;">' +
                         '<span class="drag-handle" style="display: none;"><i class="material-icons">drag_handle</i></span>' +
                         (asset.type || '연금저축') +
                     '</td>' +
                     '<td style="color: #424242 !important;">' + (asset.account_name || '-') + '</td>' +
                     '<td style="color: #424242 !important;">' + (asset.item_name || '-') + '</td>' +
                     '<td class="positive balance-cell ' + editableClass + ' current-value-cell" style="font-weight: bold;' + (editableClass ? ' cursor: pointer;' : '') + '" ' +
                         'data-asset-id="' + asset.id + '" data-original-value="' + currentValue + '" data-field="current_value">' +
                         '₩' + currentValue.toLocaleString() +
                     '</td>' +
                     '<td class="positive balance-cell ' + editableClass + ' deposit-amount-cell" style="font-weight: bold;' + (editableClass ? ' cursor: pointer;' : '') + '" ' +
                         'data-asset-id="' + asset.id + '" data-original-value="' + depositAmount + '" data-field="deposit_amount">' +
                         '₩' + depositAmount.toLocaleString() +
                     '</td>' +
                     '<td class="' + returnClass + '" style="font-weight: bold;">' + returnRate + '%</td>' +
                     '</tr>');
        tbody.append($row);

        // 카드 생성 (모바일용)
        let $card = $('<div class="asset-card" data-asset-id="' + asset.id + '" ' +
                      'data-type="' + (asset.type || '연금저축') + '" ' +
                      'data-account="' + (asset.account_name || '') + '" ' +
                      'data-item-name="' + (asset.item_name || '') + '" ' +
                      'data-current-value="' + currentValue + '" ' +
                      'data-deposit-amount="' + depositAmount + '">' +
                      '<div class="asset-card-header">' +
                          '<div class="asset-card-title">' + (asset.item_name || '-') + '</div>' +
                          '<div class="asset-card-type">' + (asset.type || '연금저축') + '</div>' +
                          '<div class="mobile-drag-handle"><i class="material-icons">drag_handle</i></div>' +
                      '</div>' +
                      '<div class="asset-card-row">' +
                          '<div class="asset-card-label">계좌</div>' +
                          '<div class="asset-card-value">' + (asset.account_name || '-') + '</div>' +
                      '</div>' +
                      '<div class="asset-card-row">' +
                          '<div class="asset-card-label">평가금액</div>' +
                          '<div class="asset-card-balance balance-cell editable current-value-cell" ' +
                              'data-asset-id="' + asset.id + '" data-original-value="' + currentValue + '" data-field="current_value">' +
                              '₩' + currentValue.toLocaleString() +
                          '</div>' +
                      '</div>' +
                      '<div class="asset-card-row">' +
                          '<div class="asset-card-label">납입잔액</div>' +
                          '<div class="asset-card-balance balance-cell editable deposit-amount-cell" ' +
                              'data-asset-id="' + asset.id + '" data-original-value="' + depositAmount + '" data-field="deposit_amount">' +
                              '₩' + depositAmount.toLocaleString() +
                          '</div>' +
                      '</div>' +
                      '<div class="asset-card-row">' +
                          '<div class="asset-card-label">수익률</div>' +
                          '<div class="asset-card-percentage ' + returnClass + '">' + returnRate + '%</div>' +
                      '</div>' +
                      '</div>');
        cardsContainer.append($card);
    });

    // 총합 및 수익률 계산
    let totalReturnRate = totalDepositAmount > 0 ? ((totalCurrentValue - totalDepositAmount) / totalDepositAmount * 100).toFixed(2) : '0.00';
    let totalReturnClass = parseFloat(totalReturnRate) >= 0 ? 'positive' : 'negative';
    let totalProfit = totalCurrentValue - totalDepositAmount;

    // 총합 행 추가 (테이블만)
    let totalRow = '<tr style="background-color: #f5f5f5; font-weight: bold;">' +
                   '<td colspan="3" style="color: #424242 !important; text-align: right;">총 연금자산:</td>' +
                   '<td class="positive" style="font-weight: bold;">₩' + totalCurrentValue.toLocaleString() + '</td>' +
                   '<td class="positive" style="font-weight: bold;">₩' + totalDepositAmount.toLocaleString() + '</td>' +
                   '<td class="' + totalReturnClass + '" style="font-weight: bold;">' + totalReturnRate + '%</td>' +
                   '</tr>';
    tbody.append(totalRow);

    // 총합 카드 추가 (모바일만)
    let totalCard = '<div class="asset-card" style="border-left-color: #9C27B0; background-color: #f8f9fa;">' +
                    '<div class="asset-card-header">' +
                        '<div class="asset-card-title" style="color: #9C27B0;">총 연금자산</div>' +
                    '</div>' +
                    '<div class="asset-card-row">' +
                        '<div class="asset-card-label">총 평가금액</div>' +
                        '<div style="font-weight: bold; color: #9C27B0; font-size: 1.2em;">₩' + totalCurrentValue.toLocaleString() + '</div>' +
                    '</div>' +
                    '<div class="asset-card-row">' +
                        '<div class="asset-card-label">총 납입잔액</div>' +
                        '<div style="font-weight: bold; color: #424242;">₩' + totalDepositAmount.toLocaleString() + '</div>' +
                    '</div>' +
                    '<div class="asset-card-row">' +
                        '<div class="asset-card-label">수익률</div>' +
                        '<div class="asset-card-percentage ' + totalReturnClass + '" style="font-weight: bold;">' + totalReturnRate + '%</div>' +
                    '</div>' +
                    '<div class="asset-card-row">' +
                        '<div class="asset-card-label">수익금</div>' +
                        '<div class="' + totalReturnClass + '" style="font-weight: bold;">' + (totalProfit >= 0 ? '+' : '') + '₩' + Math.abs(totalProfit).toLocaleString() + '</div>' +
                    '</div>' +
                    '</div>';
    cardsContainer.append(totalCard);

    // 편집 이벤트 리스너 업데이트
    setupPensionBalanceEditing();

    // 총자산현황 업데이트
    updateTotalAssets();
}

function updateInvestmentAssetsTable(assets) {
    console.log('[DEBUG] updateInvestmentAssetsTable() 호출됨, assets:', assets);

    let tbody = $('#investment-assets-detail-table');
    let cardsContainer = $('#investment-assets-detail-cards');

    console.log('[DEBUG] tbody 요소:', tbody.length, 'cardsContainer 요소:', cardsContainer.length);

    tbody.empty();
    cardsContainer.empty();

    // 투자자산 데이터를 전역 변수로 저장 (자산군별 비중 계산용)
    window.investmentAssetsData = assets;

    let totalBalance = 0;

    // 아카이브 모드 확인
    const isArchiveMode = (typeof ArchiveManager !== 'undefined' && ArchiveManager.isArchiveMode());
    const editableClass = isArchiveMode ? '' : 'editable';

    if (!assets || assets.length === 0) {
        console.log('[DEBUG] 투자자산 데이터가 비어있음');
        tbody.append('<tr><td colspan="5" class="center-align">저축 + 투자 자산이 없습니다.</td></tr>');
        cardsContainer.append('<div class="center-align">저축 + 투자 자산이 없습니다.</div>');
        return;
    }

    console.log('[DEBUG] 투자자산 개수:', assets.length);

    // 자산 목록 표시 (테이블과 카드 모두)
    assets.forEach(function(asset, index) {
        console.log('[DEBUG] 투자자산 처리 중:', index, asset);
        // current_value가 있으면 투자자산, balance가 있으면 저축자산으로 처리
        let assetBalance = parseInt(asset.current_value || asset.balance || 0);
        totalBalance += assetBalance;
        console.log('[DEBUG] 자산 잔액:', assetBalance);

        // 구분 매핑: category -> 구분
        let assetType = asset.category || asset.type || '저축';
        if (assetType === '주식' || assetType === 'ETF' || assetType === '펀드' || assetType === '채권' || assetType === '리츠') {
            assetType = asset.category;
        } else if (assetType === '현금') {
            assetType = '저축';
        } else {
            assetType = asset.category || '혼합';
        }

        // 테이블 행 생성 (데스크톱용)
        let $row = $('<tr class="asset-row" data-asset-id="' + asset.id + '" ' +
                     'data-type="' + assetType + '" ' +
                     'data-account="' + (asset.account_name || '') + '" ' +
                     'data-item-name="' + (asset.item_name || '') + '" ' +
                     'data-balance="' + assetBalance + '">' +
                     '<td style="color: #424242 !important;">' +
                         '<span class="drag-handle" style="display: none;"><i class="material-icons">drag_handle</i></span>' +
                         assetType +
                     '</td>' +
                     '<td style="color: #424242 !important;">' + (asset.account_name || '-') + '</td>' +
                     '<td style="color: #424242 !important;">' + (asset.item_name || '-') + '</td>' +
                     '<td class="positive balance-cell ' + editableClass + '" style="font-weight: bold;' + (editableClass ? ' cursor: pointer;' : '') + '" ' +
                         'data-asset-id="' + asset.id + '" data-original-balance="' + assetBalance + '">' +
                         '₩' + assetBalance.toLocaleString() +
                     '</td>' +
                     '<td style="color: #424242 !important;">' + (asset.percentage || 0) + '%</td>' +
                     '</tr>');
        console.log('[DEBUG] 테이블 행 생성:', $row);
        tbody.append($row);
        console.log('[DEBUG] 테이블 행 추가 완료, 현재 tbody 자식 수:', tbody.children().length);

        // 카드 생성 (모바일용)
        let $card = $('<div class="asset-card" data-asset-id="' + asset.id + '" ' +
                      'data-type="' + assetType + '" ' +
                      'data-account="' + (asset.account_name || '') + '" ' +
                      'data-item-name="' + (asset.item_name || '') + '" ' +
                      'data-balance="' + assetBalance + '">' +
                      '<div class="asset-card-header">' +
                          '<div class="asset-card-title">' + (asset.item_name || '-') + '</div>' +
                          '<div class="asset-card-type">' + assetType + '</div>' +
                          '<div class="mobile-drag-handle"><i class="material-icons">drag_handle</i></div>' +
                      '</div>' +
                      '<div class="asset-card-row">' +
                          '<div class="asset-card-label">계좌</div>' +
                          '<div class="asset-card-value">' + (asset.account_name || '-') + '</div>' +
                      '</div>' +
                      '<div class="asset-card-row">' +
                          '<div class="asset-card-label">잔액</div>' +
                          '<div class="asset-card-balance balance-cell ' + editableClass + '" ' +
                              'data-asset-id="' + asset.id + '" data-original-balance="' + assetBalance + '">' +
                              '₩' + assetBalance.toLocaleString() +
                          '</div>' +
                      '</div>' +
                      '<div class="asset-card-row">' +
                          '<div class="asset-card-label">비중</div>' +
                          '<div class="asset-card-percentage">' + (asset.percentage || 0) + '%</div>' +
                      '</div>' +
                      '</div>');
        console.log('[DEBUG] 카드 생성:', $card);
        cardsContainer.append($card);
        console.log('[DEBUG] 카드 추가 완료, 현재 cardsContainer 자식 수:', cardsContainer.children().length);
    });

    console.log('[DEBUG] 모든 투자자산 처리 완료, 총 잔액:', totalBalance);

    // 총합 행 추가 (테이블만)
    let totalRow = '<tr style="background-color: #f5f5f5; font-weight: bold;">' +
                   '<td colspan="3" style="color: #424242 !important; text-align: right;">총 저축 + 투자 자산:</td>' +
                   '<td class="positive" style="font-weight: bold;">₩' + totalBalance.toLocaleString() + '</td>' +
                   '<td style="color: #424242 !important;">100%</td>' +
                   '</tr>';
    tbody.append(totalRow);

    // 총합 카드 추가 (모바일만)
    let totalCard = '<div class="asset-card" style="border-left-color: #FF9800; background-color: #f8f9fa;">' +
                    '<div class="asset-card-header">' +
                        '<div class="asset-card-title" style="color: #FF9800;">총 저축 + 투자 자산</div>' +
                    '</div>' +
                    '<div class="asset-card-row">' +
                        '<div class="asset-card-label">총 잔액</div>' +
                        '<div style="font-weight: bold; color: #FF9800; font-size: 1.2em;">₩' + totalBalance.toLocaleString() + '</div>' +
                    '</div>' +
                    '<div class="asset-card-row">' +
                        '<div class="asset-card-label">비중</div>' +
                        '<div class="asset-card-percentage" style="font-weight: bold;">100%</div>' +
                    '</div>' +
                    '</div>';
    cardsContainer.append(totalCard);

    // 잔액 편집 이벤트 리스너 업데이트
    setupBalanceEditing();

    // 더블클릭/롱프레스 이벤트 리스너 업데이트
    setupRowEditing();

    // 투자자산 더블클릭/롱프레스 이벤트 리스너 추가
    setupInvestmentRowEditing();

    // 총자산현황 업데이트
    updateTotalAssets();

    // 자산군별 비중 업데이트
    updateAssetAllocation(assets);
}

function updateCashAssetsTable(assets) {
    let tbody = $('#cash-assets-detail-table');
    let cardsContainer = $('#cash-assets-detail-cards');

    tbody.empty();
    cardsContainer.empty();

    // 현금성 자산 데이터를 전역 변수로 저장 (자산군별 비중 계산용)
    window.cashAssetsData = assets;

    let totalBalance = 0;

    // 아카이브 모드 확인
    const isArchiveMode = (typeof ArchiveManager !== 'undefined' && ArchiveManager.isArchiveMode());
    const editableClass = isArchiveMode ? '' : 'editable';

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
                     '<td class="positive balance-cell ' + editableClass + '" style="font-weight: bold;' + (editableClass ? ' cursor: pointer;' : '') + '" ' +
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
                          '<div class="asset-card-balance balance-cell ' + editableClass + '" ' +
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

    // 총자산현황 업데이트
    updateTotalAssets();

    // 자산군별 비중 업데이트 (투자자산 데이터가 있을 때만)
    if (window.investmentAssetsData && window.investmentAssetsData.length > 0) {
        updateAssetAllocation(window.investmentAssetsData);
    }
}

function setupBalanceEditing() {
    let currentlyEditing = null;

    // 잔액 셀 클릭 이벤트 (현금자산 + 투자자산)
    $('#cash-assets-detail-table .balance-cell.editable, #cash-assets-detail-cards .balance-cell.editable, #investment-assets-detail-table .balance-cell.editable, #investment-assets-detail-cards .balance-cell.editable').off('click').on('click', function() {
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
        // 클릭 이벤트 리스너 일시적으로 제거 (confirm 다이얼로그 버튼 클릭 중복 방지)
        $(document).off('click.balance-edit');

        // 변경사항이 있으면 확인
        if (typeof Feedback !== 'undefined') {
            Feedback.confirm('잔액을 ₩' + newBalance.toLocaleString() + '(으)로 수정하시겠습니까?', function() {
                // 확인
                updateAssetBalance(assetId, newBalance, cell);
                currentlyEditing = null;
                // 이벤트 리스너 재등록
                setupBalanceEditing();
            }, function() {
                // 취소
                restoreOriginalBalance(cell);
                currentlyEditing = null;
                // 이벤트 리스너 재등록
                setupBalanceEditing();
            });
        } else {
            // Fallback
            if (confirm('잔액을 ₩' + newBalance.toLocaleString() + '(으)로 수정하시겠습니까?')) {
                updateAssetBalance(assetId, newBalance, cell);
            } else {
                restoreOriginalBalance(cell);
            }
            currentlyEditing = null;
            // 이벤트 리스너 재등록
            setupBalanceEditing();
        }
    } else {
        // 변경사항 없으면 그냥 복원
        restoreOriginalBalance(cell);
        currentlyEditing = null;
    }
}

function handleEditCancel(cell) {
    restoreOriginalBalance(cell);
    currentlyEditing = null;
}

function restoreOriginalBalance(cell) {
    let originalBalance = parseInt(cell.data('original-balance'));
    cell.html('₩' + originalBalance.toLocaleString());
}

function updateAssetBalance(assetId, newBalance, cell) {
    // 로딩 표시 - 더 구체적인 스피너 사용
    cell.html('<div class="preloader-wrapper active" style="width: 20px; height: 20px; display: inline-block;"><div class="spinner-layer spinner-blue-only"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div> 수정중...');

    // 자산 유형 판별 (어느 테이블에 속해있는지 확인)
    let assetType = 'cash'; // 기본값
    let reloadFunction = loadCashAssets;
    if (cell.closest('#investment-assets-detail-table, #investment-assets-detail-cards').length > 0) {
        assetType = 'investment';
        reloadFunction = loadInvestmentAssets;
    } else if (cell.closest('#pension-assets-detail-table, #pension-assets-detail-cards').length > 0) {
        assetType = 'pension';
        reloadFunction = loadPensionAssets;
    }

    // 아카이브 모드에서는 수정 불가
    const isArchive = (typeof ArchiveManager !== 'undefined' && ArchiveManager.isArchiveMode());

    if (isArchive) {
        cell.html('₩' + cell.data('original-balance').toLocaleString());
        M.toast({html: '아카이브 데이터는 수정할 수 없습니다', classes: 'orange'});
        return;
    }

    // 현재 모드에서만 수정 가능
    let apiUrl = `${API_BASE_URL}/${assetType}-assets/${assetId}`;
    let successMessage = '잔액이 수정되었습니다.';

    // 자산 유형에 따라 필드명 결정
    let fieldName = 'balance'; // 기본값 (cash)
    if (assetType === 'investment' || assetType === 'pension') {
        fieldName = 'current_value';
    }
    let payload = {};
    payload[fieldName] = newBalance;

    $.ajax({
        url: apiUrl,
        method: 'PATCH',
        xhrFields: {
            withCredentials: true
        },
        contentType: 'application/json',
        timeout: 15000, // 15초 타임아웃
        data: JSON.stringify(payload),
        success: function(response) {
            if (response.success) {
                // 성공 시 새로운 값으로 업데이트
                cell.data('original-balance', newBalance);
                cell.html('₩' + newBalance.toLocaleString());

                // 성공 메시지 (짧게 표시)
                showSuccessMessage(successMessage);

                // 전체 테이블 새로고침 (비중 재계산을 위해)
                setTimeout(function() {
                    reloadFunction();
                }, 500);
            } else {
                let errorMessage = response.message || '알 수 없는 오류가 발생했습니다';
                showError('수정 실패: ' + errorMessage);
                restoreOriginalBalance(cell);
            }
        },
        error: function(xhr, status, error) {
            let errorMessage = '수정 중 오류가 발생했습니다';

            if (status === 'timeout') {
                errorMessage = '서버 응답 시간이 초과되었습니다';
            } else if (xhr.status === 0) {
                errorMessage = '서버에 연결할 수 없습니다';
            } else if (xhr.status === 403) {
                errorMessage = '권한이 없습니다';
            } else if (xhr.status === 404) {
                errorMessage = '해당 자산을 찾을 수 없습니다';
            } else if (xhr.status >= 500) {
                errorMessage = '서버 내부 오류가 발생했습니다';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }

            showError(errorMessage);
            restoreOriginalBalance(cell);

            console.error('잔액 수정 오류:', {
                status: status,
                error: error,
                xhr: xhr,
                assetId: assetId,
                newBalance: newBalance
            });
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

function setupInvestmentRowEditing() {
    let longPressTimer;
    let isLongPress = false;

    // 테이블 행과 카드 모두에서 이벤트 제거
    $('#investment-assets-detail-table .asset-row, #investment-assets-detail-cards .asset-card').off('dblclick touchstart touchend touchmove');

    // 데스크톱: 더블클릭 이벤트 (테이블 행)
    $('#investment-assets-detail-table').off('dblclick', '.asset-row').on('dblclick', '.asset-row', function(e) {
        // 잔액 셀은 제외 (인라인 편집 우선)
        if (!$(e.target).hasClass('balance-cell')) {
            console.log('[DEBUG] 투자자산 테이블 행 더블클릭됨');
            openInvestmentEditModal($(this));
        }
    });

    // 모바일: 카드 롱프레스 이벤트
    $('#investment-assets-detail-cards').off('touchstart', '.asset-card').on('touchstart', '.asset-card', function(e) {
        const $this = $(this);
        isLongPress = false;

        longPressTimer = setTimeout(function() {
            isLongPress = true;
            console.log('[DEBUG] 투자자산 카드 롱프레스됨');
            openInvestmentEditModal($this);
        }, 800);
    });

    $('#investment-assets-detail-cards').off('touchend', '.asset-card').on('touchend', '.asset-card', function() {
        clearTimeout(longPressTimer);
        if (!isLongPress) {
            // 일반 터치 처리 (필요시)
        }
    });

    $('#investment-assets-detail-cards').off('touchmove', '.asset-card').on('touchmove', '.asset-card', function() {
        clearTimeout(longPressTimer);
    });
}

function openInvestmentEditModal($row) {
    const assetId = $row.data('asset-id');
    const category = $row.data('type');
    const account = $row.data('account');
    const itemName = $row.data('item-name');
    const balance = $row.data('balance');

    console.log('[DEBUG] 투자자산 편집 모달 열기:', {assetId, category, account, itemName, balance});

    // 모달 폼에 데이터 채우기
    $('#edit-investment-type').val(category);
    $('#edit-investment-account').val(account);
    $('#edit-investment-item-name').val(itemName);
    $('#edit-investment-balance').val(balance);

    // 모달 데이터 저장
    $('#edit-investment-modal').data('asset-id', assetId);

    // Materialize 컴포넌트 업데이트
    M.updateTextFields();
    M.FormSelect.init(document.getElementById('edit-investment-type'));

    // 모달 열기
    const modal = M.Modal.getInstance(document.getElementById('edit-investment-modal'));
    modal.open();
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
        url: '' + API_BASE_URL + '/cash-assets/' + assetId,
        method: 'PUT',
        xhrFields: {
            withCredentials: true
        },
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
        url: '' + API_BASE_URL + '/cash-assets',
        method: 'POST',
        xhrFields: {
            withCredentials: true
        },
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

function setupPensionBalanceEditing() {
    let currentlyEditing = null;

    // 편집 가능한 셀 클릭 이벤트
    $('.current-value-cell.editable, .deposit-amount-cell.editable').off('click').on('click', function() {
        if (currentlyEditing && currentlyEditing[0] !== this) {
            // 다른 셀이 편집중이면 먼저 처리
            handlePensionEditCancel(currentlyEditing);
        }

        if ($(this).find('input').length > 0) {
            return; // 이미 편집중이면 무시
        }

        startPensionBalanceEdit($(this));
        currentlyEditing = $(this);
    });

    // 테이블 행과 카드 모두에서 이벤트 제거
    $('#pension-assets-detail-table .asset-row, #pension-assets-detail-cards .asset-card').off('dblclick touchstart touchend touchmove');

    // 데스크톱: 더블클릭 이벤트 (테이블 행)
    $('#pension-assets-detail-table').off('dblclick', '.asset-row').on('dblclick', '.asset-row', function(e) {
        // 잔액 셀은 제외 (인라인 편집 우선)
        if (!$(e.target).hasClass('balance-cell')) {
            e.preventDefault();
            if (currentlyEditing) {
                handlePensionEditComplete(currentlyEditing);
                currentlyEditing = null;
            }
            openPensionEditModal($(this));
        }
    });

    // 모바일: 카드 롱프레스 이벤트
    let pensionLongPressTimer;
    let pensionIsLongPress = false;

    $('#pension-assets-detail-cards').off('touchstart', '.asset-card').on('touchstart', '.asset-card', function(e) {
        if ($(e.target).hasClass('balance-cell')) return; // 잔액 셀 제외

        const $card = $(this);
        pensionIsLongPress = false;

        // 햅틱 피드백 대신 시각적 피드백
        pensionLongPressTimer = setTimeout(function() {
            pensionIsLongPress = true;
            $card.addClass('long-press-active');

            // 진동 효과 (지원되는 경우)
            if ('vibrate' in navigator) {
                navigator.vibrate(100);
            }

            if (currentlyEditing) {
                handlePensionEditComplete(currentlyEditing);
                currentlyEditing = null;
            }
            openPensionEditModal($card);

            setTimeout(function() {
                $card.removeClass('long-press-active');
            }, 300);
        }, 600); // 600ms 롱프레스
    });

    // 카드 터치 이벤트 정리
    $('#pension-assets-detail-cards').off('touchend touchmove', '.asset-card').on('touchend touchmove', '.asset-card', function() {
        clearTimeout(pensionLongPressTimer);
    });

    // 일반 터치는 롱프레스가 아닐 때만 처리
    $('#pension-assets-detail-cards').off('touchend', '.asset-card').on('touchend', '.asset-card', function() {
        if (!pensionIsLongPress) {
            // 일반 터치 처리 (필요시)
        }
    });

    // 다른 곳 클릭 시 편집 완료 확인
    $(document).off('click.pension-balance-edit').on('click.pension-balance-edit', function(e) {
        if (currentlyEditing && !$(e.target).closest('.current-value-cell, .deposit-amount-cell').length) {
            handlePensionEditComplete(currentlyEditing);
            currentlyEditing = null;
        }
    });
}

function startPensionBalanceEdit(cell) {
    let originalValue = parseInt(cell.data('original-value'));
    let assetId = cell.data('asset-id');
    let field = cell.data('field');

    // 현재 내용을 input으로 교체
    let input = $('<input type="number" class="balance-input" value="' + originalValue + '" ' +
                 'style="width: 100%; border: 2px solid #9C27B0; padding: 5px; text-align: right; font-weight: bold;" ' +
                 'min="0" step="1000">');

    cell.html(input);
    input.focus().select();

    // Enter 키로 확인
    input.on('keydown', function(e) {
        if (e.key === 'Enter') {
            handlePensionEditComplete(cell);
            $(document).off('click.pension-balance-edit');
        } else if (e.key === 'Escape') {
            handlePensionEditCancel(cell);
            $(document).off('click.pension-balance-edit');
        }
    });
}

function handlePensionEditComplete(cell) {
    let input = cell.find('.balance-input');
    if (input.length === 0) return;

    let newValue = parseInt(input.val()) || 0;
    let originalValue = parseInt(cell.data('original-value'));
    let assetId = cell.data('asset-id');
    let field = cell.data('field');
    let fieldName = field === 'current_value' ? '평가금액' : '납입잔액';

    if (newValue !== originalValue) {
        // 변경사항이 있으면 확인
        if (confirm(fieldName + '을 ₩' + newValue.toLocaleString() + '(으)로 수정하시겠습니까?')) {
            updatePensionAssetValue(assetId, field, newValue, cell);
        } else {
            // 취소 시 원래 값으로 복원
            restorePensionOriginalValue(cell);
        }
    } else {
        // 변경사항 없으면 그냥 복원
        restorePensionOriginalValue(cell);
    }
}

function handlePensionEditCancel(cell) {
    restorePensionOriginalValue(cell);
}

function restorePensionOriginalValue(cell) {
    let originalValue = parseInt(cell.data('original-value'));
    cell.html('₩' + originalValue.toLocaleString());
}

function updatePensionAssetValue(assetId, field, newValue, cell) {
    // 로딩 표시
    cell.html('<i class="material-icons" style="font-size: 18px;">hourglass_empty</i> 수정중...');

    let updateData = {};
    updateData[field] = newValue;

    $.ajax({
        url: '' + API_BASE_URL + '/pension-assets/' + assetId,
        method: 'PUT',
        xhrFields: {
            withCredentials: true
        },
        contentType: 'application/json',
        data: JSON.stringify(updateData),
        success: function(response) {
            if (response.success) {
                // 성공 시 새로운 값으로 업데이트
                cell.data('original-value', newValue);
                cell.html('₩' + newValue.toLocaleString());

                // 성공 메시지 (짧게 표시)
                showSuccessMessage('연금자산이 수정되었습니다.');

                // 전체 테이블 새로고침 (수익률 재계산을 위해)
                setTimeout(function() {
                    loadPensionAssets();
                }, 500);
            } else {
                showError('수정 실패: ' + response.message);
                restorePensionOriginalValue(cell);
            }
        },
        error: function(xhr, status, error) {
            showError('수정 중 오류 발생: ' + error);
            restorePensionOriginalValue(cell);
        }
    });
}

function saveNewPensionAsset() {
    console.log('[DEBUG] saveNewPensionAsset() 호출됨');

    const formData = {
        type: $('#add-pension-type').val(),
        item_name: $('#add-pension-item-name').val(),
        current_value: parseInt($('#add-pension-current-value').val()) || 0,
        deposit_amount: parseInt($('#add-pension-deposit-amount').val()) || 0
    };

    console.log('[DEBUG] 연금자산 폼 데이터:', formData);

    // 간단한 클라이언트 검증
    if (!formData.type || !formData.item_name.trim()) {
        console.log('[DEBUG] 유효성 검사 실패 - type:', formData.type, 'item_name:', formData.item_name);
        M.toast({html: '연금유형과 종목명을 입력해주세요.', classes: 'red'});
        return;
    }

    console.log('[DEBUG] 유효성 검사 통과, API 호출 시작');

    // API 호출
    $.ajax({
        url: '' + API_BASE_URL + '/pension-assets',
        method: 'POST',
        xhrFields: {
            withCredentials: true
        },
        contentType: 'application/json',
        data: JSON.stringify(formData),
        beforeSend: function() {
            console.log('[DEBUG] 연금자산 API 호출 시작, URL: ' + API_BASE_URL + '/pension-assets');
            console.log('[DEBUG] 전송 데이터:', JSON.stringify(formData));
        },
        success: function(response) {
            console.log('[DEBUG] 연금자산 API 응답 성공:', response);
            if (response.success) {
                console.log('[DEBUG] 연금자산 추가 성공, 모달 닫기 시작');

                // 모달 닫기
                const modal = M.Modal.getInstance(document.getElementById('add-pension-modal'));
                modal.close();

                // 폼 초기화
                $('#add-pension-form')[0].reset();
                M.updateTextFields();
                M.FormSelect.init(document.getElementById('add-pension-type'));

                // 성공 메시지
                M.toast({html: '새 연금자산이 추가되었습니다.', classes: 'green'});

                // 테이블 새로고침
                console.log('[DEBUG] 연금자산 테이블 새로고침 시작');
                setTimeout(function() {
                    loadPensionAssets();
                }, 500);
            } else {
                console.log('[DEBUG] 연금자산 추가 실패:', response.message);
                M.toast({html: '추가 실패: ' + response.message, classes: 'red'});
            }
        },
        error: function(xhr, status, error) {
            console.log('[DEBUG] 연금자산 API 오류 발생 - status:', status, 'error:', error, 'xhr:', xhr);
            M.toast({html: '추가 중 오류 발생: ' + error, classes: 'red'});
        }
    });
}

function saveNewInvestmentAsset() {
    console.log('[DEBUG] saveNewInvestmentAsset() 호출됨');

    const currentValue = parseInt($('#add-investment-balance').val()) || 0;
    const formData = {
        category: $('#add-investment-type').val(),
        account_name: $('#add-investment-account').val() || '투자계좌',
        item_name: $('#add-investment-item-name').val(),
        current_value: currentValue,
        deposit_amount: currentValue // 투자원금을 현재가치와 동일하게 설정
    };

    console.log('[DEBUG] 투자자산 폼 데이터:', formData);

    // 간단한 클라이언트 검증
    if (!formData.category || !formData.item_name.trim()) {
        console.log('[DEBUG] 유효성 검사 실패 - category:', formData.category, 'item_name:', formData.item_name);
        M.toast({html: '투자유형과 종목명을 입력해주세요.', classes: 'red'});
        return;
    }

    console.log('[DEBUG] 유효성 검사 통과, API 호출 시작');

    // API 호출
    $.ajax({
        url: '' + API_BASE_URL + '/investment-assets',
        method: 'POST',
        xhrFields: {
            withCredentials: true
        },
        contentType: 'application/json',
        data: JSON.stringify(formData),
        beforeSend: function() {
            console.log('[DEBUG] 투자자산 API 호출 시작, URL: ' + API_BASE_URL + '/investment-assets');
            console.log('[DEBUG] 전송 데이터:', JSON.stringify(formData));
        },
        success: function(response) {
            console.log('[DEBUG] 투자자산 API 응답 성공:', response);
            if (response.success) {
                console.log('[DEBUG] 투자자산 추가 성공, 모달 닫기 시작');

                // 모달 닫기
                const modal = M.Modal.getInstance(document.getElementById('add-investment-modal'));
                modal.close();

                // 폼 초기화
                $('#add-investment-form')[0].reset();
                M.updateTextFields();
                M.FormSelect.init(document.getElementById('add-investment-type'));

                // 성공 메시지
                M.toast({html: '새 투자자산이 추가되었습니다.', classes: 'green'});

                // 테이블 새로고침
                console.log('[DEBUG] 투자자산 테이블 새로고침 시작');

                // 즉시 새로고침 시도
                console.log('[DEBUG] 즉시 loadInvestmentAssets 호출 시도');
                loadInvestmentAssets();

                setTimeout(function() {
                    console.log('[DEBUG] setTimeout 콜백 실행됨');
                    console.log('[DEBUG] loadInvestmentAssets 함수 타입:', typeof loadInvestmentAssets);
                    console.log('[DEBUG] loadInvestmentAssets 함수:', loadInvestmentAssets);
                    loadInvestmentAssets();
                }, 500);
            } else {
                console.log('[DEBUG] 투자자산 추가 실패:', response.message);
                M.toast({html: '추가 실패: ' + response.message, classes: 'red'});
            }
        },
        error: function(xhr, status, error) {
            console.log('[DEBUG] 투자자산 API 오류 발생 - status:', status, 'error:', error, 'xhr:', xhr);
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
        url: '' + API_BASE_URL + '/cash-assets/reorder',
        method: 'PUT',
        xhrFields: {
            withCredentials: true
        },
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

let isInvestmentReorderMode = false;

function toggleInvestmentReorderMode() {
    isInvestmentReorderMode = !isInvestmentReorderMode;
    const $toggle = $('#investment-reorder-toggle');
    const $tbody = $('#investment-assets-detail-table');
    const $cardsContainer = $('#investment-assets-detail-cards');

    if (isInvestmentReorderMode) {
        // 순서 변경 모드 활성화
        $toggle.removeClass('blue').addClass('orange').html('<i class="material-icons left">check</i>완료');

        // 테이블과 카드 모두에 sortable-enabled 클래스 추가
        $tbody.addClass('sortable-enabled');
        $cardsContainer.addClass('sortable-enabled');

        // 드래그 핸들 표시
        $('#investment-assets-detail-table .drag-handle, #investment-assets-detail-cards .mobile-drag-handle').show();

        // 테이블 sortable 활성화 (데스크톱)
        $tbody.sortable({
            handle: '.drag-handle',
            helper: 'clone',
            placeholder: 'ui-sortable-placeholder',
            start: function(e, ui) {
                ui.placeholder.height(ui.item.height());
            },
            stop: function(e, ui) {
                saveInvestmentNewOrder();
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
                saveInvestmentNewOrder();
            }
        });

        // 편집 기능 비활성화
        $('#investment-assets-detail-table .balance-cell.editable, #investment-assets-detail-cards .balance-cell.editable').removeClass('editable').addClass('disabled-while-sorting');

        M.toast({html: '드래그하여 순서를 변경하세요', classes: 'blue'});
    } else {
        // 일반 모드로 복원
        $toggle.removeClass('orange').addClass('blue').html('<i class="material-icons left">swap_vert</i>순서변경');

        // sortable-enabled 클래스 제거
        $tbody.removeClass('sortable-enabled');
        $cardsContainer.removeClass('sortable-enabled');

        // 드래그 핸들 숨기기
        $('#investment-assets-detail-table .drag-handle, #investment-assets-detail-cards .mobile-drag-handle').hide();

        // jQuery UI sortable 비활성화
        if ($tbody.hasClass('ui-sortable')) {
            $tbody.sortable('destroy');
        }
        if ($cardsContainer.hasClass('ui-sortable')) {
            $cardsContainer.sortable('destroy');
        }

        // 편집 기능 복원
        $('#investment-assets-detail-table .disabled-while-sorting, #investment-assets-detail-cards .disabled-while-sorting').addClass('editable').removeClass('disabled-while-sorting');

        M.toast({html: '순서 변경이 완료되었습니다', classes: 'green'});
    }
}

function saveInvestmentNewOrder() {
    const orders = [];

    // 현재 보이는 컨테이너(데스크톱: 테이블, 모바일: 카드)에서 순서 가져오기
    if ($(window).width() > 768) {
        // 데스크톱: 테이블 행에서 순서 가져오기
        $('#investment-assets-detail-table .asset-row').each(function(index) {
            const assetId = $(this).data('asset-id');
            if (assetId) {
                orders.push({
                    id: parseInt(assetId)
                });
            }
        });
    } else {
        // 모바일: 카드에서 순서 가져오기
        $('#investment-assets-detail-cards .asset-card').each(function(index) {
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
        url: '' + API_BASE_URL + '/investment-assets/reorder',
        method: 'PUT',
        xhrFields: {
            withCredentials: true
        },
        contentType: 'application/json',
        data: JSON.stringify({
            orders: orders
        }),
        success: function(response) {
            if (response.success) {
                // 순서가 성공적으로 저장됨
                console.log('Investment order updated successfully');
            } else {
                M.toast({html: '순서 저장 실패: ' + response.message, classes: 'red'});
                // 실패시 테이블 새로고침
                loadInvestmentAssets();
            }
        },
        error: function(xhr, status, error) {
            M.toast({html: '순서 저장 중 오류 발생: ' + error, classes: 'red'});
            // 실패시 테이블 새로고침
            loadInvestmentAssets();
        }
    });
}

let isPensionReorderMode = false;

function togglePensionReorderMode() {
    isPensionReorderMode = !isPensionReorderMode;
    const $toggle = $('#pension-reorder-toggle');
    const $tbody = $('#pension-assets-detail-table');
    const $cardsContainer = $('#pension-assets-detail-cards');

    if (isPensionReorderMode) {
        // 순서 변경 모드 활성화
        $toggle.removeClass('blue').addClass('orange').html('<i class="material-icons left">check</i>완료');

        // 테이블과 카드 모두에 sortable-enabled 클래스 추가
        $tbody.addClass('sortable-enabled');
        $cardsContainer.addClass('sortable-enabled');

        // 드래그 핸들 표시
        $('#pension-assets-detail-table .drag-handle, #pension-assets-detail-cards .mobile-drag-handle').show();

        // 테이블 sortable 활성화 (데스크톱)
        $tbody.sortable({
            handle: '.drag-handle',
            helper: 'clone',
            placeholder: 'ui-sortable-placeholder',
            start: function(e, ui) {
                ui.placeholder.height(ui.item.height());
            },
            stop: function(e, ui) {
                savePensionNewOrder();
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
                savePensionNewOrder();
            }
        });

        // 편집 기능 비활성화
        $('#pension-assets-detail-table .balance-cell.editable, #pension-assets-detail-cards .balance-cell.editable').removeClass('editable').addClass('disabled-while-sorting');

        M.toast({html: '드래그하여 순서를 변경하세요', classes: 'blue'});
    } else {
        // 일반 모드로 복원
        $toggle.removeClass('orange').addClass('blue').html('<i class="material-icons left">swap_vert</i>순서변경');

        // sortable-enabled 클래스 제거
        $tbody.removeClass('sortable-enabled');
        $cardsContainer.removeClass('sortable-enabled');

        // 드래그 핸들 숨기기
        $('#pension-assets-detail-table .drag-handle, #pension-assets-detail-cards .mobile-drag-handle').hide();

        // jQuery UI sortable 비활성화
        if ($tbody.hasClass('ui-sortable')) {
            $tbody.sortable('destroy');
        }
        if ($cardsContainer.hasClass('ui-sortable')) {
            $cardsContainer.sortable('destroy');
        }

        // 편집 기능 복원
        $('#pension-assets-detail-table .disabled-while-sorting, #pension-assets-detail-cards .disabled-while-sorting').addClass('editable').removeClass('disabled-while-sorting');

        M.toast({html: '순서 변경이 완료되었습니다', classes: 'green'});
    }
}

function savePensionNewOrder() {
    const orders = [];

    // 현재 보이는 컨테이너(데스크톱: 테이블, 모바일: 카드)에서 순서 가져오기
    if ($(window).width() > 768) {
        // 데스크톱: 테이블 행에서 순서 가져오기
        $('#pension-assets-detail-table .asset-row').each(function(index) {
            const assetId = $(this).data('asset-id');
            if (assetId) {
                orders.push({
                    id: parseInt(assetId)
                });
            }
        });
    } else {
        // 모바일: 카드에서 순서 가져오기
        $('#pension-assets-detail-cards .asset-card').each(function(index) {
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
        url: '' + API_BASE_URL + '/pension-assets/reorder',
        method: 'PUT',
        xhrFields: {
            withCredentials: true
        },
        contentType: 'application/json',
        data: JSON.stringify({
            orders: orders
        }),
        success: function(response) {
            if (response.success) {
                // 순서가 성공적으로 저장됨
                console.log('Pension order updated successfully');
            } else {
                M.toast({html: '순서 저장 실패: ' + response.message, classes: 'red'});
                // 실패시 테이블 새로고침
                loadPensionAssets();
            }
        },
        error: function(xhr, status, error) {
            M.toast({html: '순서 저장 중 오류 발생: ' + error, classes: 'red'});
            // 실패시 테이블 새로고침
            loadPensionAssets();
        }
    });
}

function openPensionEditModal($row) {
    const assetId = $row.data('asset-id');
    const type = $row.data('type');
    const account = $row.data('account');
    const itemName = $row.data('item-name');

    // 모달 폼에 데이터 채우기
    $('#edit-pension-type').val(type);
    $('#edit-pension-account').val(account || '');
    $('#edit-pension-item-name').val(itemName);

    // 모달에 자산 ID 저장
    $('#edit-pension-modal').data('asset-id', assetId);

    // Materialize 컴포넌트 업데이트
    M.updateTextFields();
    M.FormSelect.init(document.getElementById('edit-pension-type'));

    // 모달 열기
    const modal = M.Modal.getInstance(document.getElementById('edit-pension-modal'));
    modal.open();
}

function saveEditedPensionAsset() {
    const assetId = $('#edit-pension-modal').data('asset-id');
    const formData = {
        type: $('#edit-pension-type').val(),
        item_name: $('#edit-pension-item-name').val()
    };

    // 간단한 클라이언트 검증
    if (!formData.type || !formData.item_name.trim()) {
        M.toast({html: '연금유형과 종목명을 입력해주세요.', classes: 'red'});
        return;
    }

    // API 호출
    $.ajax({
        url: '' + API_BASE_URL + '/pension-assets/' + assetId,
        method: 'PUT',
        xhrFields: {
            withCredentials: true
        },
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function(response) {
            if (response.success) {
                // 모달 닫기
                const modal = M.Modal.getInstance(document.getElementById('edit-pension-modal'));
                modal.close();

                // 성공 메시지
                M.toast({html: '연금자산 정보가 수정되었습니다.', classes: 'green'});

                // 테이블 새로고침
                setTimeout(function() {
                    loadPensionAssets();
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

// 현금성 자산 삭제 함수
function deleteAsset() {
    const assetId = $('#edit-modal').data('asset-id');

    if (!confirm('이 자산을 삭제하시겠습니까?\n삭제된 데이터는 복구할 수 없습니다.')) {
        return;
    }

    $.ajax({
        url: '' + API_BASE_URL + '/cash-assets/' + assetId,
        method: 'DELETE',
        xhrFields: {
            withCredentials: true
        },
        success: function(response) {
            if (response.success) {
                // 모달 닫기
                const modal = M.Modal.getInstance(document.getElementById('edit-modal'));
                modal.close();

                // 성공 메시지
                M.toast({html: '자산이 삭제되었습니다.', classes: 'green'});

                // 테이블 새로고침
                setTimeout(function() {
                    loadCashAssets();
                }, 500);
            } else {
                M.toast({html: '삭제 실패: ' + response.message, classes: 'red'});
            }
        },
        error: function(xhr, status, error) {
            M.toast({html: '삭제 중 오류 발생: ' + error, classes: 'red'});
        }
    });
}

// 연금자산 삭제 함수
function deletePensionAsset() {
    const assetId = $('#edit-pension-modal').data('asset-id');

    if (!confirm('이 연금자산을 삭제하시겠습니까?\n삭제된 데이터는 복구할 수 없습니다.')) {
        return;
    }

    $.ajax({
        url: '' + API_BASE_URL + '/pension-assets/' + assetId,
        method: 'DELETE',
        xhrFields: {
            withCredentials: true
        },
        success: function(response) {
            if (response.success) {
                // 모달 닫기
                const modal = M.Modal.getInstance(document.getElementById('edit-pension-modal'));
                modal.close();

                // 성공 메시지
                M.toast({html: '연금자산이 삭제되었습니다.', classes: 'green'});

                // 테이블 새로고침
                setTimeout(function() {
                    loadPensionAssets();
                }, 500);
            } else {
                M.toast({html: '삭제 실패: ' + response.message, classes: 'red'});
            }
        },
        error: function(xhr, status, error) {
            M.toast({html: '삭제 중 오류 발생: ' + error, classes: 'red'});
        }
    });
}

// 투자자산 편집 저장 함수
function saveEditedInvestmentAsset() {
    console.log('[DEBUG] saveEditedInvestmentAsset() 호출됨');

    const assetId = $('#edit-investment-modal').data('asset-id');
    const formData = {
        category: $('#edit-investment-type').val(),
        account_name: $('#edit-investment-account').val() || '투자계좌',
        item_name: $('#edit-investment-item-name').val(),
        current_value: parseInt($('#edit-investment-balance').val()) || 0
    };

    console.log('[DEBUG] 투자자산 편집 데이터:', {assetId, formData});

    // 간단한 클라이언트 검증
    if (!formData.category || !formData.item_name.trim()) {
        M.toast({html: '투자유형과 종목명을 입력해주세요.', classes: 'red'});
        return;
    }

    // deposit_amount는 current_value와 동일하게 설정
    formData.deposit_amount = formData.current_value;

    $.ajax({
        url: '' + API_BASE_URL + '/investment-assets/' + assetId,
        method: 'PUT',
        xhrFields: {
            withCredentials: true
        },
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function(response) {
            console.log('[DEBUG] 투자자산 편집 API 응답:', response);
            if (response.success) {
                // 모달 닫기
                const modal = M.Modal.getInstance(document.getElementById('edit-investment-modal'));
                modal.close();

                // 성공 메시지
                M.toast({html: '투자자산이 수정되었습니다.', classes: 'green'});

                // 테이블 새로고침
                setTimeout(function() {
                    loadInvestmentAssets();
                }, 500);
            } else {
                M.toast({html: '수정 실패: ' + response.message, classes: 'red'});
            }
        },
        error: function(xhr, status, error) {
            console.log('[DEBUG] 투자자산 편집 오류:', {xhr, status, error});
            M.toast({html: '수정 중 오류 발생: ' + error, classes: 'red'});
        }
    });
}

// 투자자산 삭제 함수
function deleteInvestmentAsset() {
    console.log('[DEBUG] deleteInvestmentAsset() 호출됨');

    const assetId = $('#edit-investment-modal').data('asset-id');

    if (!confirm('이 투자자산을 삭제하시겠습니까?\n삭제된 데이터는 복구할 수 없습니다.')) {
        return;
    }

    console.log('[DEBUG] 투자자산 삭제 요청:', assetId);

    $.ajax({
        url: '' + API_BASE_URL + '/investment-assets/' + assetId,
        method: 'DELETE',
        xhrFields: {
            withCredentials: true
        },
        success: function(response) {
            console.log('[DEBUG] 투자자산 삭제 API 응답:', response);
            if (response.success) {
                // 모달 닫기
                const modal = M.Modal.getInstance(document.getElementById('edit-investment-modal'));
                modal.close();

                // 성공 메시지
                M.toast({html: '투자자산이 삭제되었습니다.', classes: 'green'});

                // 테이블 새로고침
                setTimeout(function() {
                    loadInvestmentAssets();
                }, 500);
            } else {
                M.toast({html: '삭제 실패: ' + response.message, classes: 'red'});
            }
        },
        error: function(xhr, status, error) {
            console.log('[DEBUG] 투자자산 삭제 오류:', {xhr, status, error});
            M.toast({html: '삭제 중 오류 발생: ' + error, classes: 'red'});
        }
    });
}

// 총자산현황 업데이트 함수
function updateTotalAssets() {
    let cashTotal = 0;
    let investmentTotal = 0;
    let pensionTotal = 0;

    // 현금성 자산 합계 계산
    $('#cash-assets-detail-table .asset-row').each(function() {
        const balanceText = $(this).find('.balance-cell').text().replace(/[,원₩]/g, '').trim();
        const balance = parseInt(balanceText) || 0;
        cashTotal += balance;
    });

    // 저축+투자 자산 합계 계산
    $('#investment-assets-detail-table .asset-row').each(function() {
        const balanceText = $(this).find('.balance-cell').text().replace(/[,원₩]/g, '').trim();
        const balance = parseInt(balanceText) || 0;
        investmentTotal += balance;
    });

    // 연금 자산 합계 계산 (평가금액만)
    $('#pension-assets-detail-table .asset-row').each(function() {
        const balanceText = $(this).find('.current-value-cell').text().replace(/[,원₩]/g, '').trim();
        const balance = parseInt(balanceText) || 0;
        pensionTotal += balance;
    });

    const totalAll = cashTotal + investmentTotal + pensionTotal;

    // UI 업데이트
    $('#total-cash-assets').text(formatCurrency(cashTotal));
    $('#total-investment-assets').text(formatCurrency(investmentTotal));
    $('#total-pension-assets').text(formatCurrency(pensionTotal));
    $('#total-all-assets').text(formatCurrency(totalAll));
}

// 통화 포맷팅 함수
function formatCurrency(amount) {
    if (amount === 0) return '0원';
    return Math.round(amount).toLocaleString() + '원';
}

// 자산군별 비중 업데이트 함수
function updateAssetAllocation(assets) {
    let tbody = $('#asset-allocation-table');
    let cardsContainer = $('#asset-allocation-cards');

    tbody.empty();
    cardsContainer.empty();

    if (!assets || assets.length === 0) {
        tbody.append('<tr><td colspan="3" class="center-align">자산이 없습니다.</td></tr>');
        cardsContainer.append('<div class="center-align">자산이 없습니다.</div>');
        return;
    }

    // 자산군별 합계 계산
    let categoryTotals = {};
    let totalAmount = 0;

    assets.forEach(function(asset) {
        const balance = parseInt(asset.current_value || asset.balance || 0);
        const category = asset.category || asset.type || '기타';

        // 혼합형의 경우 현금(30%), 주식(70%)로 분리
        if (category === '혼합') {
            // 현금 부분 (30%)
            if (!categoryTotals['현금']) {
                categoryTotals['현금'] = 0;
            }
            categoryTotals['현금'] += Math.round(balance * 0.3);

            // 주식 부분 (70%)
            if (!categoryTotals['주식']) {
                categoryTotals['주식'] = 0;
            }
            categoryTotals['주식'] += Math.round(balance * 0.7);
        } else {
            if (!categoryTotals[category]) {
                categoryTotals[category] = 0;
            }
            categoryTotals[category] += balance;
        }

        totalAmount += balance;
    });

    // 현금성 자산도 포함 (전역에서 가져오기) - 모두 현금으로 분류
    if (window.cashAssetsData && window.cashAssetsData.length > 0) {
        window.cashAssetsData.forEach(function(asset) {
            const balance = parseInt(asset.balance || 0);

            // 현금성 자산은 모두 현금 카테고리 (혼합형에서 분리된 현금과 합산)
            if (!categoryTotals['현금']) {
                categoryTotals['현금'] = 0;
            }
            categoryTotals['현금'] += balance;

            totalAmount += balance;
        });
    }

    // 자산군별 비중 테이블 생성
    Object.keys(categoryTotals).sort().forEach(function(category) {
        const amount = categoryTotals[category];
        const percentage = totalAmount > 0 ? ((amount / totalAmount) * 100).toFixed(2) : 0;

        // 테이블 행 추가
        let row = '<tr>' +
                  '<td style="color: #424242 !important;">' + category + '</td>' +
                  '<td class="positive" style="font-weight: bold;">₩' + amount.toLocaleString() + '</td>' +
                  '<td style="color: #424242 !important; font-weight: bold;">' + percentage + '%</td>' +
                  '</tr>';
        tbody.append(row);

        // 모바일 카드 추가
        let card = '<div class="asset-card" style="margin-bottom: 10px;">' +
                   '<div class="asset-card-header">' +
                       '<div class="asset-card-title">' + category + '</div>' +
                   '</div>' +
                   '<div class="asset-card-row">' +
                       '<div class="asset-card-label">잔액</div>' +
                       '<div class="asset-card-balance" style="font-weight: bold;">₩' + amount.toLocaleString() + '</div>' +
                   '</div>' +
                   '<div class="asset-card-row">' +
                       '<div class="asset-card-label">비중</div>' +
                       '<div class="asset-card-percentage" style="font-weight: bold;">' + percentage + '%</div>' +
                   '</div>' +
                   '</div>';
        cardsContainer.append(card);
    });

    // 총합 행 추가 (테이블만)
    let totalRow = '<tr style="background-color: #f5f5f5; font-weight: bold;">' +
                   '<td style="color: #424242 !important; text-align: right;">총계:</td>' +
                   '<td class="positive" style="font-weight: bold;">₩' + totalAmount.toLocaleString() + '</td>' +
                   '<td style="color: #424242 !important; font-weight: bold;">100.00%</td>' +
                   '</tr>';
    tbody.append(totalRow);
}

function showError(message) {
    $('#loading').hide();
    $('#error-message .card-content span.card-title').text('오류 발생');
    $('#error-message .card-content p').text(message);
    $('#error-message').show();
}

// =================== 아카이브 관리 시스템 ===================

// 기존 함수들 백업 (오리지널 보존) - 즉시 초기화
const OriginalAssetAPI = {
    loadCashAssets: function() {
        $.ajax({
            url: '' + API_BASE_URL + '/cash-assets',
            method: 'GET',
        xhrFields: {
            withCredentials: true
        },
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
    },

    loadInvestmentAssets: function() {
        console.log('[DEBUG] OriginalAssetAPI.loadInvestmentAssets() 호출됨');
        $.ajax({
            url: '' + API_BASE_URL + '/investment-assets',
            method: 'GET',
        xhrFields: {
            withCredentials: true
        },
            success: function(response) {
                console.log('[DEBUG] OriginalAssetAPI 투자자산 API 응답:', response);
                if (response.success) {
                    console.log('[DEBUG] response.data:', response.data);
                    console.log('[DEBUG] response.data.data:', response.data.data);
                    console.log('[DEBUG] 전달할 데이터 타입:', typeof response.data.data, '배열 여부:', Array.isArray(response.data.data));

                    // 올바른 데이터 구조 사용
                    const data = response.data.data || response.data;
                    console.log('[DEBUG] 최종 전달 데이터:', data);
                    updateInvestmentAssetsTable(data);
                } else {
                    showError('투자 자산 데이터 로드 실패: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                showError('투자 자산 서버 연결 실패: ' + error);
            }
        });
    },

    loadPensionAssets: function() {
        console.log('[DEBUG] OriginalAssetAPI.loadPensionAssets() 호출됨');
        $.ajax({
            url: '' + API_BASE_URL + '/pension-assets',
            method: 'GET',
        xhrFields: {
            withCredentials: true
        },
            success: function(response) {
                console.log('[DEBUG] OriginalAssetAPI 연금자산 API 응답:', response);
                if (response.success) {
                    console.log('[DEBUG] 연금자산 response.data:', response.data);
                    console.log('[DEBUG] 연금자산 response.data.data:', response.data.data);
                    // 올바른 데이터 구조 사용 (연금자산은 response.data에 직접 배열)
                    const data = response.data.data || response.data;
                    console.log('[DEBUG] 연금자산 최종 전달 데이터:', data);
                    updatePensionAssetsTable(data);
                } else {
                    showError('연금 자산 데이터 로드 실패: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('[DEBUG] 연금자산 서버 연결 실패:', error);
                showError('연금 자산 서버 연결 실패: ' + error);
            }
        });
    }
};

// Archive Manager 클래스
class ArchiveManager {
    static currentMode = 'current';
    static selectedMonth = null;
    static availableMonths = [];

    // 초기화
    static init() {
        this.loadAvailableMonths();
        this.setupEventHandlers();
    }

    // 아카이브 월 목록 로드
    static loadAvailableMonths() {
        // 로딩 상태 표시
        this.showMonthSelectorLoading();

        $.ajax({
            url: '' + API_BASE_URL + '/archive/months',
            method: 'GET',
        xhrFields: {
            withCredentials: true
        },
            timeout: 10000, // 10초 타임아웃
            success: (response) => {
                this.hideMonthSelectorLoading();
                if (response.success && response.data) {
                    this.availableMonths = response.data;
                    this.populateMonthSelector();
                } else {
                    this.showMonthSelectorError('아카이브 월 목록을 불러올 수 없습니다: ' + (response.message || '알 수 없는 오류'));
                }
            },
            error: (xhr, status, error) => {
                this.hideMonthSelectorLoading();
                let errorMessage = '아카이브 월 목록 로드 실패';

                if (status === 'timeout') {
                    errorMessage = '서버 응답 시간이 초과되었습니다';
                } else if (xhr.status === 0) {
                    errorMessage = '서버에 연결할 수 없습니다';
                } else if (xhr.status >= 500) {
                    errorMessage = '서버 내부 오류가 발생했습니다';
                } else if (xhr.status === 404) {
                    errorMessage = '아카이브 API를 찾을 수 없습니다';
                }

                this.showMonthSelectorError(errorMessage);
                console.error('아카이브 월 목록 로드 오류:', error, xhr);
            }
        });
    }

    // 월 선택기 옵션 추가
    static populateMonthSelector() {
        const selector = $('#month-selector');

        // 기존 아카이브 옵션 제거 (current는 유지)
        selector.find('option:not([value="current"])').remove();

        // 아카이브 월 추가
        this.availableMonths.forEach(month => {
            selector.append(`<option value="${month.value}">${month.label}</option>`);
        });
    }

    // 이벤트 핸들러 설정
    static setupEventHandlers() {
        // 월 선택기 변경 이벤트
        $('#month-selector').on('change', (e) => {
            const selectedValue = e.target.value;
            this.switchMode(selectedValue);
        });

        // 스냅샷 생성 버튼
        $('#create-snapshot-btn').on('click', () => {
            this.createSnapshot();
        });
    }

    // 모드 전환 (현재 vs 아카이브)
    static switchMode(month) {
        if (month === 'current') {
            this.currentMode = 'current';
            this.selectedMonth = null;
            this.hideArchiveNotice();

            // 현재 모드로 돌아갈 때는 로딩 화면 표시하지 않고 바로 데이터 로드
            try {
                // 원본 함수들로 데이터 로드
                OriginalAssetAPI.loadCashAssets();
                OriginalAssetAPI.loadInvestmentAssets();
                OriginalAssetAPI.loadPensionAssets();

                // 데이터 로드 완료 후 총계 업데이트
                setTimeout(() => {
                    // 총계 업데이트
                    if (typeof updateTotalAssets === 'function') {
                        updateTotalAssets();
                    }
                }, 500);
            } catch (error) {
                this.showDataError('현재 데이터 로드 중 오류가 발생했습니다');
                console.error('현재 데이터 로드 오류:', error);
            }

        } else {
            this.currentMode = 'archive';
            this.selectedMonth = month;
            this.showArchiveNotice(month);

            // 아카이브 데이터 로드
            this.loadArchiveData();
        }
    }

    // 아카이브 알림 표시
    static showArchiveNotice(month) {
        const monthInfo = this.availableMonths.find(m => m.value === month);
        const monthLabel = monthInfo ? monthInfo.label : month;

        $('#archive-notice-text').text(`${monthLabel} 아카이브 데이터 조회 중 - 수정 시 아카이브가 업데이트됩니다`);
        $('#archive-mode-notice').show();
    }

    // 아카이브 알림 숨기기
    static hideArchiveNotice() {
        $('#archive-mode-notice').hide();
    }

    // 아카이브 데이터 로드
    static loadArchiveData() {
        // 각 자산 유형별로 아카이브 데이터 로드
        this.loadArchiveCashAssets();
        this.loadArchiveInvestmentAssets();
        this.loadArchivePensionAssets();
    }

    // API URL 생성
    static getAPIUrl(endpoint) {
        if (this.currentMode === 'current') {
            return `${API_BASE_URL}/${endpoint}`;
        } else {
            return `${API_BASE_URL}/archive/${endpoint}?month=${this.selectedMonth}`;
        }
    }

    // 아카이브 현금 자산 로드
    static loadArchiveCashAssets() {
        // 로딩 상태 표시
        this.showDataLoading();

        $.ajax({
            url: this.getAPIUrl('cash-assets'),
            method: 'GET',
        xhrFields: {
            withCredentials: true
        },
            timeout: 10000, // 10초 타임아웃
            success: (response) => {
                this.hideDataLoading();
                if (response.success) {
                    // 기존 함수와 동일한 형태로 데이터 전달
                    const assets = response.data.data || response.data;
                    updateCashAssetsTable(assets);
                    updateTotalAssets();
                } else {
                    this.showDataError('현금 자산 아카이브를 불러올 수 없습니다: ' + (response.message || '알 수 없는 오류'));
                }
            },
            error: (xhr, status, error) => {
                this.hideDataLoading();

                let errorMessage = '현금 자산 아카이브 로드 실패';

                if (status === 'timeout') {
                    errorMessage = '서버 응답 시간이 초과되었습니다';
                } else if (xhr.status === 0) {
                    errorMessage = '서버에 연결할 수 없습니다';
                } else if (xhr.status === 404) {
                    errorMessage = '해당 월의 아카이브 데이터를 찾을 수 없습니다';
                } else if (xhr.status >= 500) {
                    errorMessage = '서버 내부 오류가 발생했습니다';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                this.showDataError(errorMessage);
                console.error('현금 자산 아카이브 로드 오류:', {
                    status: status,
                    error: error,
                    xhr: xhr,
                    month: this.selectedMonth
                });
            }
        });
    }

    // 아카이브 투자 자산 로드
    static loadArchiveInvestmentAssets() {
        this.showDataLoading();

        $.ajax({
            url: this.getAPIUrl('investment-assets'),
            method: 'GET',
        xhrFields: {
            withCredentials: true
        },
            timeout: 10000,
            success: (response) => {
                this.hideDataLoading();
                if (response.success) {
                    const assets = response.data.data || response.data;
                    updateInvestmentAssetsTable(assets);
                    updateTotalAssets();
                } else {
                    this.showDataError('투자 자산 아카이브를 불러올 수 없습니다: ' + (response.message || '알 수 없는 오류'));
                }
            },
            error: (xhr, status, error) => {
                this.hideDataLoading();
                let errorMessage = '투자 자산 아카이브 로드 실패';

                if (status === 'timeout') {
                    errorMessage = '서버 응답 시간이 초과되었습니다';
                } else if (xhr.status === 0) {
                    errorMessage = '서버에 연결할 수 없습니다';
                } else if (xhr.status === 404) {
                    errorMessage = '해당 월의 투자 자산 아카이브를 찾을 수 없습니다';
                } else if (xhr.status >= 500) {
                    errorMessage = '서버 내부 오류가 발생했습니다';
                }

                this.showDataError(errorMessage);
                console.error('투자 자산 아카이브 로드 오류:', error, xhr);
            }
        });
    }

    // 아카이브 연금 자산 로드
    static loadArchivePensionAssets() {
        this.showDataLoading();

        $.ajax({
            url: this.getAPIUrl('pension-assets'),
            method: 'GET',
        xhrFields: {
            withCredentials: true
        },
            timeout: 10000,
            success: (response) => {
                this.hideDataLoading();
                if (response.success) {
                    const assets = response.data.data || response.data;
                    updatePensionAssetsTable(assets);
                    updateTotalAssets();
                } else {
                    this.showDataError('연금 자산 아카이브를 불러올 수 없습니다: ' + (response.message || '알 수 없는 오류'));
                }
            },
            error: (xhr, status, error) => {
                this.hideDataLoading();
                let errorMessage = '연금 자산 아카이브 로드 실패';

                if (status === 'timeout') {
                    errorMessage = '서버 응답 시간이 초과되었습니다';
                } else if (xhr.status === 0) {
                    errorMessage = '서버에 연결할 수 없습니다';
                } else if (xhr.status === 404) {
                    errorMessage = '해당 월의 연금 자산 아카이브를 찾을 수 없습니다';
                } else if (xhr.status >= 500) {
                    errorMessage = '서버 내부 오류가 발생했습니다';
                }

                this.showDataError(errorMessage);
                console.error('연금 자산 아카이브 로드 오류:', error, xhr);
            }
        });
    }

    // 스냅샷 생성
    static createSnapshot() {
        const currentDate = new Date();
        const month = `${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, '0')}`;

        $.ajax({
            url: `${API_BASE_URL}/archive/create-snapshot?month=${month}`,
            method: 'POST',
        xhrFields: {
            withCredentials: true
        },
            success: (response) => {
                if (response.success) {
                    M.toast({html: '현재 데이터로 스냅샷이 생성되었습니다', classes: 'green'});
                    // 월 목록 새로고침
                    this.loadAvailableMonths();
                } else {
                    M.toast({html: '스냅샷 생성 실패: ' + response.message, classes: 'red'});
                }
            },
            error: (xhr, status, error) => {
                M.toast({html: '스냅샷 생성 오류: ' + error, classes: 'red'});
            }
        });
    }

    // 현재 모드 확인
    static isArchiveMode() {
        return this.currentMode === 'archive';
    }

    // 현재 선택된 월 반환
    static getCurrentMonth() {
        return this.selectedMonth;
    }

    // 로딩 상태 표시 메서드들
    static showMonthSelectorLoading() {
        const selector = $('#month-selector');
        if (selector.length) {
            selector.prop('disabled', true);
            // 기존 옵션을 유지하고 로딩 상태만 표시
            selector.find('option:not([value="current"])').remove();
            selector.append('<option disabled>로딩 중...</option>');
        }
    }

    static hideMonthSelectorLoading() {
        const selector = $('#month-selector');
        if (selector.length) {
            selector.prop('disabled', false);
            // 로딩 옵션 제거
            selector.find('option:disabled').remove();
        }
    }

    static showMonthSelectorError(message) {
        const selector = $('#month-selector');
        if (selector.length) {
            // 현재 옵션은 유지하고 오류 옵션만 추가
            selector.find('option:not([value="current"])').remove();
            selector.append(`<option disabled>오류: ${message}</option>`);
            // 사용자에게 토스트 메시지로도 알림
            if (typeof M !== 'undefined' && M.toast) {
                M.toast({
                    html: `<i class="material-icons left">error</i>${message}`,
                    classes: 'red',
                    displayLength: 5000
                });
            }
        }
    }

    // 데이터 로딩 상태 표시
    static showDataLoading() {
        const container = $('.assets-container, .desktop-table-container');
        if (container.length) {
            const loadingHtml = `
                <div class="loading-state" style="text-align: center; padding: 40px;">
                    <div class="preloader-wrapper big active">
                        <div class="spinner-layer spinner-blue">
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
                    <p style="margin-top: 20px; color: #666;">데이터를 불러오는 중...</p>
                </div>
            `;
            container.html(loadingHtml);
        }
    }

    static hideDataLoading() {
        $('.loading-state').remove();
    }

    static showDataError(message) {
        const container = $('.assets-container, .desktop-table-container');
        if (container.length) {
            const errorHtml = `
                <div class="error-state" style="text-align: center; padding: 40px;">
                    <i class="material-icons" style="font-size: 64px; color: #f44336;">error_outline</i>
                    <h5 style="color: #666; margin: 20px 0;">데이터 로드 실패</h5>
                    <p style="color: #999; margin-bottom: 20px;">${message}</p>
                    <button class="btn waves-effect waves-light red" onclick="location.reload()">
                        <i class="material-icons left">refresh</i>새로고침
                    </button>
                </div>
            `;
            container.html(errorHtml);
        }
    }
}

// loadCashAssets 함수 재정의 (Archive Manager와 연동)
window.loadCashAssets = function() {
    if (typeof ArchiveManager !== 'undefined' && ArchiveManager.isArchiveMode()) {
        ArchiveManager.loadArchiveCashAssets();
    } else if (typeof OriginalAssetAPI !== 'undefined' && OriginalAssetAPI.loadCashAssets) {
        OriginalAssetAPI.loadCashAssets();
    } else {
        console.error('OriginalAssetAPI.loadCashAssets is not available');
    }
};

window.loadInvestmentAssets = function() {
    if (typeof ArchiveManager !== 'undefined' && ArchiveManager.isArchiveMode()) {
        ArchiveManager.loadArchiveInvestmentAssets();
    } else if (typeof OriginalAssetAPI !== 'undefined' && OriginalAssetAPI.loadInvestmentAssets) {
        OriginalAssetAPI.loadInvestmentAssets();
    } else {
        console.error('OriginalAssetAPI.loadInvestmentAssets is not available');
    }
};

window.loadPensionAssets = function() {
    if (typeof ArchiveManager !== 'undefined' && ArchiveManager.isArchiveMode()) {
        ArchiveManager.loadArchivePensionAssets();
    } else if (typeof OriginalAssetAPI !== 'undefined' && OriginalAssetAPI.loadPensionAssets) {
        OriginalAssetAPI.loadPensionAssets();
    } else {
        console.error('OriginalAssetAPI.loadPensionAssets is not available');
    }
};

// 페이지 로드 시 Archive Manager 초기화
$(document).ready(function() {
    // 기존 초기화가 완료된 후 Archive Manager 초기화
    setTimeout(() => {
        ArchiveManager.init();
    }, 100);
});

