<?php
$pageTitle = '지출현황';
include 'includes/header.php';
?>

<style>
    /* 월 선택기 모바일 최적화 */
    @media only screen and (max-width: 600px) {
        .month-selector-row {
            flex-direction: column !important;
            gap: 10px;
        }

        .month-selector-title {
            text-align: center;
            margin-bottom: 5px;
        }

        .month-selector-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        #month-selector {
            flex: 1;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        #archive-mode-notice {
            margin: 15px 0 0 0 !important;
            padding: 12px !important;
            font-size: 14px;
        }

        #archive-mode-notice .material-icons {
            font-size: 18px !important;
        }
    }

    @media only screen and (max-width: 480px) {
        .month-selector-title {
            font-size: 16px;
        }

        #archive-controls .btn {
            font-size: 11px;
            padding: 6px 12px;
            white-space: nowrap;
        }
    }
</style>

<main class="container">
        <!-- 월별 선택기 -->
        <div class="section">
            <div class="card">
                <div class="card-content">
                    <div class="row month-selector-row" style="margin-bottom: 0;">
                        <div class="col s12 m6">
                            <h6 class="month-selector-title" style="margin: 8px 0;"><i class="material-icons left">date_range</i>조회 기간</h6>
                        </div>
                        <div class="col s12 m6">
                            <div class="month-selector-controls input-field" style="margin-top: 0;">
                                <select id="month-selector" class="browser-default">
                                    <option value="current" selected>현재 (실시간)</option>
                                    <!-- 아카이브 월 목록은 JavaScript로 동적 로드 -->
                                </select>
                            </div>
                        </div>
                    </div>
                    <div id="archive-mode-notice" class="card-panel orange lighten-4" style="display:none; margin: 10px 0 0 0; padding: 10px;">
                        <i class="material-icons left" style="margin-right: 8px;">archive</i>
                        <span id="archive-notice-text">과거 데이터 조회 중 - 수정 시 아카이브가 업데이트됩니다</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 월간 지출현황 -->
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <h5 class="section-title center-align" style="margin-bottom: 20px;">💰 월간 지출현황</h5>
                        <div class="row">
                            <div class="col s12 m4">
                                <div class="center-align">
                                    <h6 style="color: #f44336; margin: 0;">고정지출(예정)</h6>
                                    <span id="fixed-expenses-total" style="font-size: 20px; font-weight: bold; color: #f44336;">₩0</span>
                                </div>
                            </div>
                            <div class="col s12 m4">
                                <div class="center-align">
                                    <h6 style="color: #2196F3; margin: 0;">고정지출(선납)</h6>
                                    <span id="prepaid-expenses-total" style="font-size: 20px; font-weight: bold; color: #2196F3;">₩0</span>
                                </div>
                            </div>
                            <div class="col s12 m4">
                                <div class="center-align">
                                    <h6 style="color: #FF5722; margin: 0;">총 월간지출</h6>
                                    <span id="total-monthly-expenses" style="font-size: 20px; font-weight: bold; color: #FF5722;">₩0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 고정지출(예정) 섹션 -->
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <h5 class="section-title" style="margin: 0;">📋 고정지출(예정)</h5>
                            <button class="btn-floating waves-effect waves-light green modal-trigger"
                                    data-target="add-fixed-expense-modal" title="고정지출 추가" id="add-fixed-expense-btn">
                                <i class="material-icons">add</i>
                            </button>
                        </div>

                        <!-- 데스크톱용 테이블 -->
                        <div class="responsive-table hide-on-small-only">
                            <table class="striped">
                                <thead>
                                    <tr>
                                        <th>항목명</th>
                                        <th>금액</th>
                                        <th>결제일</th>
                                        <th>수단</th>
                                    </tr>
                                </thead>
                                <tbody id="fixed-expenses-table">
                                    <tr>
                                        <td colspan="4" class="center-align">데이터를 불러오는 중...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 모바일용 카드 -->
                        <div class="hide-on-med-and-up" id="fixed-expenses-cards">
                            <div class="center-align">데이터를 불러오는 중...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 고정지출(선납) 섹션 -->
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <h5 class="section-title" style="margin: 0;">💳 고정지출(선납)</h5>
                            <button class="btn-floating waves-effect waves-light blue modal-trigger"
                                    data-target="add-prepaid-expense-modal" title="선납지출 추가" id="add-prepaid-expense-btn">
                                <i class="material-icons">add</i>
                            </button>
                        </div>

                        <!-- 데스크톱용 테이블 -->
                        <div class="responsive-table hide-on-small-only">
                            <table class="striped">
                                <thead>
                                    <tr>
                                        <th>항목명</th>
                                        <th>금액</th>
                                        <th>결제일</th>
                                        <th>수단</th>
                                    </tr>
                                </thead>
                                <tbody id="prepaid-expenses-table">
                                    <tr>
                                        <td colspan="4" class="center-align">데이터를 불러오는 중...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 모바일용 카드 -->
                        <div class="hide-on-med-and-up" id="prepaid-expenses-cards">
                            <div class="center-align">데이터를 불러오는 중...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- 고정지출 추가 모달 -->
<div id="add-fixed-expense-modal" class="modal modal-fixed-footer">
    <div class="modal-content">
        <h4><i class="material-icons left">add</i>고정지출 추가</h4>
        <div class="row">
            <form id="add-fixed-expense-form" class="col s12">
                <div class="row">
                    <div class="input-field col s12">
                        <input id="add-fixed-item-name" type="text" class="validate" required maxlength="100">
                        <label for="add-fixed-item-name">항목명*</label>
                        <span class="helper-text" data-error="항목명을 입력해주세요" data-success="">최대 100자</span>
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s12 m4">
                        <input id="add-fixed-amount" type="number" class="validate" required min="0" max="999999999">
                        <label for="add-fixed-amount">금액*</label>
                        <span class="helper-text" data-error="올바른 금액을 입력해주세요" data-success="">원</span>
                    </div>
                    <div class="input-field col s12 m4">
                        <input id="add-fixed-payment-date" type="number" class="validate" min="1" max="31">
                        <label for="add-fixed-payment-date">결제일</label>
                        <span class="helper-text">1-31일 (선택사항)</span>
                    </div>
                    <div class="input-field col s12 m4">
                        <select id="add-fixed-payment-method">
                            <option value="" disabled selected>결제수단 선택</option>
                            <option value="현금">현금</option>
                            <option value="체크">체크</option>
                            <option value="신용">신용</option>
                        </select>
                        <label>결제수단*</label>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">취소</a>
        <a href="#!" class="waves-effect waves-light btn green" id="save-fixed-expense-add">
            <i class="material-icons left">save</i>저장
        </a>
    </div>
</div>

<!-- 선납지출 추가 모달 -->
<div id="add-prepaid-expense-modal" class="modal modal-fixed-footer">
    <div class="modal-content">
        <h4><i class="material-icons left">add</i>선납지출 추가</h4>
        <div class="row">
            <form id="add-prepaid-expense-form" class="col s12">
                <div class="row">
                    <div class="input-field col s12">
                        <input id="add-prepaid-item-name" type="text" class="validate" required maxlength="100">
                        <label for="add-prepaid-item-name">항목명*</label>
                        <span class="helper-text" data-error="항목명을 입력해주세요" data-success="">최대 100자</span>
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s12 m4">
                        <input id="add-prepaid-amount" type="number" class="validate" required min="0" max="999999999">
                        <label for="add-prepaid-amount">금액*</label>
                        <span class="helper-text" data-error="올바른 금액을 입력해주세요" data-success="">원</span>
                    </div>
                    <div class="input-field col s12 m4">
                        <input id="add-prepaid-payment-date" type="number" class="validate" required min="1" max="31">
                        <label for="add-prepaid-payment-date">결제일*</label>
                        <span class="helper-text" data-error="결제일을 입력해주세요" data-success="">1-31일</span>
                    </div>
                    <div class="input-field col s12 m4">
                        <select id="add-prepaid-payment-method" required>
                            <option value="" disabled selected>결제수단 선택</option>
                            <option value="현금">현금</option>
                            <option value="체크">체크</option>
                            <option value="신용">신용</option>
                        </select>
                        <label>결제수단*</label>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">취소</a>
        <a href="#!" class="waves-effect waves-light btn blue" id="save-prepaid-expense-add">
            <i class="material-icons left">save</i>저장
        </a>
    </div>
</div>

<!-- 고정지출 수정 모달 -->
<div id="edit-fixed-expense-modal" class="modal modal-fixed-footer">
    <div class="modal-content">
        <h4><i class="material-icons left">edit</i>고정지출 수정</h4>
        <div class="row">
            <form id="edit-fixed-expense-form" class="col s12">
                <input type="hidden" id="edit-fixed-expense-id">
                <div class="row">
                    <div class="input-field col s12">
                        <input id="edit-fixed-item-name" type="text" class="validate" required maxlength="100">
                        <label for="edit-fixed-item-name">항목명*</label>
                        <span class="helper-text" data-error="항목명을 입력해주세요" data-success="">최대 100자</span>
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s12 m4">
                        <input id="edit-fixed-amount" type="number" class="validate" required min="0" max="999999999">
                        <label for="edit-fixed-amount">금액*</label>
                        <span class="helper-text" data-error="올바른 금액을 입력해주세요" data-success="">원</span>
                    </div>
                    <div class="input-field col s12 m4">
                        <input id="edit-fixed-payment-date" type="number" class="validate" min="1" max="31">
                        <label for="edit-fixed-payment-date">결제일</label>
                        <span class="helper-text">1-31일 (선택사항)</span>
                    </div>
                    <div class="input-field col s12 m4">
                        <select id="edit-fixed-payment-method">
                            <option value="" disabled>결제수단 선택</option>
                            <option value="현금">현금</option>
                            <option value="체크">체크</option>
                            <option value="신용">신용</option>
                        </select>
                        <label>결제수단*</label>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#!" class="waves-effect waves-light btn red" id="delete-fixed-expense-confirm" style="float: left;">
            <i class="material-icons left">delete</i>삭제
        </a>
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">취소</a>
        <a href="#!" class="waves-effect waves-light btn green" id="save-fixed-expense-edit">
            <i class="material-icons left">save</i>저장
        </a>
    </div>
</div>

<!-- 선납지출 수정 모달 -->
<div id="edit-prepaid-expense-modal" class="modal modal-fixed-footer">
    <div class="modal-content">
        <h4><i class="material-icons left">edit</i>선납지출 수정</h4>
        <div class="row">
            <form id="edit-prepaid-expense-form" class="col s12">
                <input type="hidden" id="edit-prepaid-expense-id">
                <div class="row">
                    <div class="input-field col s12">
                        <input id="edit-prepaid-item-name" type="text" class="validate" required maxlength="100">
                        <label for="edit-prepaid-item-name">항목명*</label>
                        <span class="helper-text" data-error="항목명을 입력해주세요" data-success="">최대 100자</span>
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s12 m4">
                        <input id="edit-prepaid-amount" type="number" class="validate" required min="0" max="999999999">
                        <label for="edit-prepaid-amount">금액*</label>
                        <span class="helper-text" data-error="올바른 금액을 입력해주세요" data-success="">원</span>
                    </div>
                    <div class="input-field col s12 m4">
                        <input id="edit-prepaid-payment-date" type="number" class="validate" required min="1" max="31">
                        <label for="edit-prepaid-payment-date">결제일*</label>
                        <span class="helper-text" data-error="결제일을 입력해주세요" data-success="">1-31일</span>
                    </div>
                    <div class="input-field col s12 m4">
                        <select id="edit-prepaid-payment-method" required>
                            <option value="" disabled>결제수단 선택</option>
                            <option value="현금">현금</option>
                            <option value="체크">체크</option>
                            <option value="신용">신용</option>
                        </select>
                        <label>결제수단*</label>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#!" class="waves-effect waves-light btn red" id="delete-prepaid-expense-confirm" style="float: left;">
            <i class="material-icons left">delete</i>삭제
        </a>
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">취소</a>
        <a href="#!" class="waves-effect waves-light btn blue" id="save-prepaid-expense-edit">
            <i class="material-icons left">save</i>저장
        </a>
    </div>
</div>

<script>
$(document).ready(function() {
    // 모달 초기화
    M.Modal.init(document.querySelectorAll('.modal'));

    // 셀렉트 박스 초기화 (browser-default는 초기화 불필요)
    M.FormSelect.init(document.querySelectorAll('select:not(.browser-default)'));

    // 월별 선택기 초기화
    initMonthSelector();

    // 현재 월 데이터 로드
    loadMonthData('current');

    // 고정지출 추가 버튼 이벤트 핸들러
    $('#save-fixed-expense-add').on('click', function() {
        saveNewFixedExpense();
    });

    // 고정지출 수정 버튼 이벤트 핸들러
    $('#save-fixed-expense-edit').on('click', function() {
        saveEditedFixedExpense();
    });

    // 고정지출 삭제 버튼 이벤트 핸들러
    $('#delete-fixed-expense-confirm').on('click', function() {
        deleteFixedExpense();
    });

    // 선납지출 추가 버튼 이벤트 핸들러
    $('#save-prepaid-expense-add').on('click', function() {
        saveNewPrepaidExpense();
    });

    // 선납지출 수정 버튼 이벤트 핸들러
    $('#save-prepaid-expense-edit').on('click', function() {
        saveEditedPrepaidExpense();
    });

    // 선납지출 삭제 버튼 이벤트 핸들러
    $('#delete-prepaid-expense-confirm').on('click', function() {
        deletePrepaidExpense();
    });

    // 테이블 행 더블클릭 이벤트
    $(document).on('dblclick', '.expense-row', function() {
        const expenseId = $(this).data('id');
        openEditExpenseModal(expenseId);
    });

    // 모바일 카드 길게 터치 이벤트
    let touchTimer;
    $(document).on('touchstart', '.expense-card', function(e) {
        const expenseId = $(this).data('id');
        touchTimer = setTimeout(function() {
            openEditExpenseModal(expenseId);
        }, 800); // 800ms 길게 터치
    });

    $(document).on('touchend touchmove', '.expense-card', function() {
        clearTimeout(touchTimer);
    });

    // 모바일 카드 더블 탭 이벤트 (대안)
    $(document).on('dblclick', '.expense-card', function() {
        const expenseId = $(this).data('id');
        openEditExpenseModal(expenseId);
    });

    // 선납지출 테이블 행 더블클릭 이벤트
    $(document).on('dblclick', '.prepaid-expense-row', function() {
        const expenseId = $(this).data('id');
        openEditPrepaidExpenseModal(expenseId);
    });

    // 선납지출 모바일 카드 길게 터치 이벤트
    let prepaidTouchTimer;
    $(document).on('touchstart', '.prepaid-expense-card', function(e) {
        const expenseId = $(this).data('id');
        prepaidTouchTimer = setTimeout(function() {
            openEditPrepaidExpenseModal(expenseId);
        }, 800); // 800ms 길게 터치
    });

    $(document).on('touchend touchmove', '.prepaid-expense-card', function() {
        clearTimeout(prepaidTouchTimer);
    });

    // 선납지출 모바일 카드 더블 탭 이벤트 (대안)
    $(document).on('dblclick', '.prepaid-expense-card', function() {
        const expenseId = $(this).data('id');
        openEditPrepaidExpenseModal(expenseId);
    });

    // 월별 선택기 변경 이벤트
    $('#month-selector').on('change', function() {
        const selectedMonth = $(this).val();
        loadMonthData(selectedMonth);
    });

});

let currentViewMode = 'current'; // 'current' or 'archive'
let currentSelectedMonth = null;

function getAPIUrl(endpoint) {
    if (currentViewMode === 'current') {
        return `http://localhost:8080/api/${endpoint}`;
    } else {
        // 아카이브 모드에서는 year와 month 파라미터가 필요
        const [year, monthNum] = currentSelectedMonth.split('-');
        return `http://localhost:8080/api/expense-archive/${endpoint}?year=${year}&month=${parseInt(monthNum)}`;
    }
}

function initMonthSelector() {
    loadAvailableArchiveMonths();
}

function loadAvailableArchiveMonths() {
    $.ajax({
        url: 'http://localhost:8080/api/expense-archive/available-months',
        type: 'GET',
        timeout: 10000,
        success: function(response) {
            if (response.success && response.data) {
                populateMonthSelector(response.data);
            } else {
                showMonthSelectorError('아카이브 월 목록을 불러올 수 없습니다: ' + (response.message || '알 수 없는 오류'));
            }
        },
        error: function(xhr, status, error) {
            showMonthSelectorError('서버 연결 실패: ' + error);
        }
    });
}

function populateMonthSelector(availableMonths) {
    const selector = $('#month-selector');
    // 기존 아카이브 옵션 제거 (current는 유지)
    selector.find('option:not([value="current"])').remove();

    // 아카이브 월 추가
    availableMonths.forEach(function(month) {
        selector.append(`<option value="${month.value}">${month.label}</option>`);
    });
}

function showMonthSelectorError(message) {
    const selector = $('#month-selector');
    // 현재 옵션은 유지하고 오류 옵션만 추가
    selector.find('option:not([value="current"])').remove();
    selector.append(`<option disabled>오류: ${message}</option>`);

    if (typeof M !== 'undefined' && M.toast) {
        M.toast({
            html: message,
            classes: 'red white-text',
            displayLength: 4000
        });
    }
}

function loadCurrentMonthData() {
    currentViewMode = 'current';
    hideArchiveNotice();
    $('#add-fixed-expense-btn, #add-prepaid-expense-btn').show();
    loadFixedExpenses();
    loadPrepaidExpenses();
}

function loadMonthData(selectedMonth) {
    console.log('Loading month data for:', selectedMonth);

    // 기존 데이터 초기화
    clearExpenseData();

    currentSelectedMonth = selectedMonth;

    if (selectedMonth === 'current') {
        // 현재 월 - 실시간 데이터
        currentViewMode = 'current';
        hideArchiveNotice();
        $('#add-fixed-expense-btn, #add-prepaid-expense-btn').show();
        loadFixedExpenses();
        loadPrepaidExpenses();
    } else {
        // 과거 월 - 아카이브 데이터
        currentViewMode = 'archive';
        showArchiveNotice(selectedMonth);
        $('#add-fixed-expense-btn, #add-prepaid-expense-btn').show(); // 아카이브에서도 CRUD 허용
        loadArchiveData(selectedMonth);
    }
}

function clearExpenseData() {
    // 테이블과 카드 초기화
    $('#fixed-expenses-table').html('<tr><td colspan="4" class="center-align">데이터를 불러오는 중...</td></tr>');
    $('#prepaid-expenses-table').html('<tr><td colspan="4" class="center-align">데이터를 불러오는 중...</td></tr>');
    $('#fixed-expenses-cards').html('<div class="center-align">데이터를 불러오는 중...</div>');
    $('#prepaid-expenses-cards').html('<div class="center-align">데이터를 불러오는 중...</div>');

    // 총액 초기화
    $('#fixed-expenses-total').text('₩0');
    $('#prepaid-expenses-total').text('₩0');
    $('#total-monthly-expenses').text('₩0');
}

function showArchiveNotice(month) {
    // 월 라벨 생성 (예: "2024-08" -> "2024년 8월")
    const [year, monthNum] = month.split('-');
    const monthLabel = `${year}년 ${parseInt(monthNum)}월`;
    $('#archive-notice-text').text(`${monthLabel} 아카이브 데이터 조회 중 - 수정 시 아카이브가 업데이트됩니다`);
    $('#archive-mode-notice').show();
}

function hideArchiveNotice() {
    $('#archive-mode-notice').hide();
}

function loadArchiveData(month) {
    // 아카이브 모드에서는 개별 함수로 데이터 로드
    loadFixedExpenses();
    loadPrepaidExpenses();
}



function loadFixedExpenses() {
    let url;
    if (currentViewMode === 'current') {
        url = 'http://localhost:8080/api/fixed-expenses';
    } else {
        const [year, monthNum] = currentSelectedMonth.split('-');
        url = `http://localhost:8080/api/expense-archive/fixed-expenses?year=${year}&month=${parseInt(monthNum)}`;
    }

    $.ajax({
        url: url,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                displayFixedExpenses(response.data);
            } else {
                console.error('고정지출 API 오류:', response.message);
                showMessage('고정지출 데이터를 불러올 수 없습니다.', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('고정지출 서버 연결 오류:', {status, error});
            showMessage('서버 연결에 실패했습니다.', 'error');
        }
    });
}

function displayFixedExpenses(expenses) {
    let tbody = $('#fixed-expenses-table');
    let cardsContainer = $('#fixed-expenses-cards');

    tbody.empty();
    cardsContainer.empty();

    let totalAmount = 0;

    if (!expenses || expenses.length === 0) {
        tbody.append('<tr><td colspan="4" class="center-align">고정지출이 없습니다.</td></tr>');
        cardsContainer.append('<div class="center-align">고정지출이 없습니다.</div>');
        $('#fixed-expenses-total').text('₩0');
        updateMonthlyExpensesTotal();
        return;
    }

    // 결제일 기준으로 정렬 (NULL은 미정으로 맨 뒤에)
    expenses.sort((a, b) => {
        const dateA = a.payment_date;
        const dateB = b.payment_date;
        if (dateA === null || dateA === undefined) return 1; // A가 미정이면 뒤로
        if (dateB === null || dateB === undefined) return -1; // B가 미정이면 뒤로
        return dateA - dateB; // 일반 날짜는 오름차순
    });

    expenses.forEach(function(expense) {
        totalAmount += parseInt(expense.amount || 0);

        const paymentDate = expense.payment_date ? expense.payment_date + '일' : '-';
        const amount = parseInt(expense.amount || 0);

        // 테이블 행 추가
        let row = '<tr class="expense-row" data-id="' + expense.id + '" style="cursor: pointer;">' +
                  '<td style="color: #424242 !important;">' + (expense.item_name || '-') + '</td>' +
                  '<td class="negative" style="font-weight: bold;">₩' + amount.toLocaleString() + '</td>' +
                  '<td style="color: #424242 !important;">' + paymentDate + '</td>' +
                  '<td style="color: #424242 !important;">' + (expense.payment_method || '-') + '</td>' +
                  '</tr>';
        tbody.append(row);

        // 모바일 카드 추가
        let card = '<div class="expense-card" data-id="' + expense.id + '" style="margin-bottom: 10px; border-left: 4px solid #f44336; cursor: pointer;">' +
                   '<div class="card-content" style="padding: 12px;">' +
                       '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">' +
                           '<span style="font-weight: bold; color: #424242;">' + (expense.item_name || '-') + '</span>' +
                           '<span style="font-weight: bold; color: #f44336;">₩' + amount.toLocaleString() + '</span>' +
                       '</div>' +
                       '<div style="display: flex; justify-content: space-between; font-size: 14px; color: #666;">' +
                           '<span>📅 ' + paymentDate + '</span>' +
                           '<span>💳 ' + (expense.payment_method || '-') + '</span>' +
                       '</div>' +
                   '</div>' +
                   '</div>';
        cardsContainer.append(card);
    });

    // 총액 업데이트
    $('#fixed-expenses-total').text('₩' + totalAmount.toLocaleString());
    updateMonthlyExpensesTotal();
}

function saveNewFixedExpense() {
    // 폼 데이터 수집
    const itemName = $('#add-fixed-item-name').val().trim();
    const amount = $('#add-fixed-amount').val();
    const paymentDate = $('#add-fixed-payment-date').val();
    const paymentMethod = $('#add-fixed-payment-method').val();

    // 유효성 검사
    if (!itemName) {
        showMessage('항목명을 입력해주세요.', 'error');
        $('#add-fixed-item-name').focus();
        return;
    }

    if (!amount || amount <= 0) {
        showMessage('올바른 금액을 입력해주세요.', 'error');
        $('#add-fixed-amount').focus();
        return;
    }

    if (!paymentMethod) {
        showMessage('결제수단을 선택해주세요.', 'error');
        return;
    }

    // API 요청 데이터 준비
    const data = {
        item_name: itemName,
        amount: parseInt(amount),
        payment_method: paymentMethod
    };

    // 결제일이 입력된 경우에만 추가
    if (paymentDate && paymentDate >= 1 && paymentDate <= 31) {
        data.payment_date = parseInt(paymentDate);
    }
    // 빈 값이면 payment_date 필드를 포함하지 않음 (NULL로 처리됨)

    // API 호출
    $.ajax({
        url: getAPIUrl('fixed-expenses'),
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            if (response.success) {
                // 성공 메시지
                showMessage('새 고정지출이 추가되었습니다.', 'success');

                // 모달 닫기
                M.Modal.getInstance(document.getElementById('add-fixed-expense-modal')).close();

                // 폼 초기화
                clearFixedExpenseForm();

                // 테이블 새로고침
                setTimeout(function() {
                    loadFixedExpenses();
                }, 500);
            } else {
                showMessage(response.message || '고정지출 추가에 실패했습니다.', 'error');
            }
        },
        error: function(xhr) {
            let errorMessage = '서버 연결에 실패했습니다.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showMessage(errorMessage, 'error');
        }
    });
}

function clearFixedExpenseForm() {
    $('#add-fixed-expense-form')[0].reset();
    M.FormSelect.init(document.querySelectorAll('select:not(.browser-default)'));
    M.updateTextFields();
}

function openEditExpenseModal(expenseId) {
    // API에서 고정지출 정보 가져오기
    $.ajax({
        url: getAPIUrl('fixed-expenses') + '/' + expenseId,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const expense = response.data;

                // 폼에 데이터 채우기
                $('#edit-fixed-expense-id').val(expense.id);
                $('#edit-fixed-item-name').val(expense.item_name);
                $('#edit-fixed-amount').val(expense.amount);
                $('#edit-fixed-payment-date').val(expense.payment_date || '');
                $('#edit-fixed-payment-method').val(expense.payment_method);

                // 라벨 업데이트
                M.updateTextFields();
                M.FormSelect.init(document.querySelectorAll('select:not(.browser-default)'));

                // 모달 열기
                M.Modal.getInstance(document.getElementById('edit-fixed-expense-modal')).open();
            } else {
                showMessage('고정지출 정보를 불러올 수 없습니다.', 'error');
            }
        },
        error: function() {
            showMessage('서버 연결에 실패했습니다.', 'error');
        }
    });
}

function saveEditedFixedExpense() {
    const expenseId = $('#edit-fixed-expense-id').val();
    const itemName = $('#edit-fixed-item-name').val().trim();
    const amount = $('#edit-fixed-amount').val();
    const paymentDate = $('#edit-fixed-payment-date').val();
    const paymentMethod = $('#edit-fixed-payment-method').val();

    // 유효성 검사
    if (!itemName) {
        showMessage('항목명을 입력해주세요.', 'error');
        $('#edit-fixed-item-name').focus();
        return;
    }

    if (!amount || amount <= 0) {
        showMessage('올바른 금액을 입력해주세요.', 'error');
        $('#edit-fixed-amount').focus();
        return;
    }

    if (!paymentMethod) {
        showMessage('결제수단을 선택해주세요.', 'error');
        return;
    }

    // API 요청 데이터 준비
    const data = {
        item_name: itemName,
        amount: parseInt(amount),
        payment_method: paymentMethod
    };

    // 결제일 처리 (빈 값이면 null, 값이 있으면 정수)
    if (paymentDate && paymentDate >= 1 && paymentDate <= 31) {
        data.payment_date = parseInt(paymentDate);
    } else {
        data.payment_date = null; // 빈 값일 때 명시적으로 null 전송
    }

    // API 호출
    $.ajax({
        url: getAPIUrl('fixed-expenses') + '/' + expenseId,
        type: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            if (response.success) {
                showMessage('고정지출이 수정되었습니다.', 'success');
                M.Modal.getInstance(document.getElementById('edit-fixed-expense-modal')).close();

                setTimeout(function() {
                    loadFixedExpenses();
                }, 500);
            } else {
                showMessage(response.message || '고정지출 수정에 실패했습니다.', 'error');
            }
        },
        error: function(xhr) {
            let errorMessage = '서버 연결에 실패했습니다.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showMessage(errorMessage, 'error');
        }
    });
}


function deleteFixedExpense() {
    const expenseId = $('#edit-fixed-expense-id').val();

    if (!expenseId) {
        showMessage('삭제할 고정지출을 선택해주세요.', 'error');
        return;
    }

    if (!confirm('정말로 이 고정지출을 삭제하시겠습니까?')) {
        return;
    }

    $.ajax({
        url: getAPIUrl('fixed-expenses') + '/' + expenseId,
        type: 'DELETE',
        success: function(response) {
            if (response.success) {
                showMessage('고정지출이 삭제되었습니다.', 'success');
                M.Modal.getInstance(document.getElementById('edit-fixed-expense-modal')).close();

                setTimeout(function() {
                    loadFixedExpenses();
                }, 500);
            } else {
                showMessage(response.message || '고정지출 삭제에 실패했습니다.', 'error');
            }
        },
        error: function(xhr) {
            let errorMessage = '서버 연결에 실패했습니다.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showMessage(errorMessage, 'error');
        }
    });
}

function loadPrepaidExpenses() {
    let url;
    if (currentViewMode === 'current') {
        url = 'http://localhost:8080/api/prepaid-expenses';
    } else {
        const [year, monthNum] = currentSelectedMonth.split('-');
        url = `http://localhost:8080/api/expense-archive/prepaid-expenses?year=${year}&month=${parseInt(monthNum)}`;
    }

    $.ajax({
        url: url,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                displayPrepaidExpenses(response.data);
            } else {
                console.error('선납지출 API 오류:', response.message);
                showMessage('선납지출 데이터를 불러올 수 없습니다.', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('선납지출 서버 연결 오류:', {status, error});
            showMessage('서버 연결에 실패했습니다.', 'error');
        }
    });
}

function displayPrepaidExpenses(expenses) {
    let tbody = $('#prepaid-expenses-table');
    let cardsContainer = $('#prepaid-expenses-cards');

    tbody.empty();
    cardsContainer.empty();

    if (!expenses || expenses.length === 0) {
        tbody.append('<tr><td colspan="4" class="center-align">선납지출이 없습니다.</td></tr>');
        cardsContainer.append('<div class="center-align">선납지출이 없습니다.</div>');
        $('#prepaid-expenses-total').text('₩0');
        updateMonthlyExpensesTotal();
        return;
    }

    // 결제일 기준으로 정렬
    expenses.sort((a, b) => {
        return a.payment_date - b.payment_date;
    });

    expenses.forEach(function(expense) {
        const paymentDate = expense.payment_date + '일';
        const amount = parseInt(expense.amount || 0);

        // 테이블 행 추가
        let row = '<tr class="prepaid-expense-row" data-id="' + expense.id + '" style="cursor: pointer;">' +
                  '<td style="color: #424242 !important;">' + (expense.item_name || '-') + '</td>' +
                  '<td class="negative" style="font-weight: bold;">₩' + amount.toLocaleString() + '</td>' +
                  '<td style="color: #424242 !important;">' + paymentDate + '</td>' +
                  '<td style="color: #424242 !important;">' + (expense.payment_method || '-') + '</td>' +
                  '</tr>';
        tbody.append(row);

        // 모바일 카드 추가
        let card = '<div class="prepaid-expense-card" data-id="' + expense.id + '" style="margin-bottom: 10px; border-left: 4px solid #2196F3; cursor: pointer;">' +
                   '<div class="card-content" style="padding: 12px;">' +
                       '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">' +
                           '<span style="font-weight: bold; color: #424242;">' + (expense.item_name || '-') + '</span>' +
                           '<span style="font-weight: bold; color: #2196F3;">₩' + amount.toLocaleString() + '</span>' +
                       '</div>' +
                       '<div style="display: flex; justify-content: space-between; font-size: 14px; color: #666;">' +
                           '<span>📅 ' + paymentDate + '</span>' +
                           '<span>💳 ' + (expense.payment_method || '-') + '</span>' +
                       '</div>' +
                   '</div>' +
                   '</div>';
        cardsContainer.append(card);
    });

    // 선납지출 총액 업데이트
    let totalAmount = expenses.reduce((sum, expense) => sum + parseInt(expense.amount || 0), 0);
    $('#prepaid-expenses-total').text('₩' + totalAmount.toLocaleString());
    updateMonthlyExpensesTotal();
}

function saveNewPrepaidExpense() {
    // 폼 데이터 수집
    const itemName = $('#add-prepaid-item-name').val().trim();
    const amount = $('#add-prepaid-amount').val();
    const paymentDate = $('#add-prepaid-payment-date').val();
    const paymentMethod = $('#add-prepaid-payment-method').val();

    // 유효성 검사
    if (!itemName) {
        showMessage('항목명을 입력해주세요.', 'error');
        $('#add-prepaid-item-name').focus();
        return;
    }

    if (!amount || amount <= 0) {
        showMessage('올바른 금액을 입력해주세요.', 'error');
        $('#add-prepaid-amount').focus();
        return;
    }

    if (!paymentDate || paymentDate < 1 || paymentDate > 31) {
        showMessage('올바른 결제일을 입력해주세요.', 'error');
        $('#add-prepaid-payment-date').focus();
        return;
    }

    if (!paymentMethod) {
        showMessage('결제수단을 선택해주세요.', 'error');
        return;
    }

    // API 요청 데이터 준비
    const data = {
        item_name: itemName,
        amount: parseInt(amount),
        payment_date: parseInt(paymentDate),
        payment_method: paymentMethod
    };

    // API 호출
    $.ajax({
        url: getAPIUrl('prepaid-expenses'),
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            if (response.success) {
                showMessage('새 선납지출이 추가되었습니다.', 'success');
                M.Modal.getInstance(document.getElementById('add-prepaid-expense-modal')).close();
                clearPrepaidExpenseForm();

                setTimeout(function() {
                    loadPrepaidExpenses();
                }, 500);
            } else {
                showMessage(response.message || '선납지출 추가에 실패했습니다.', 'error');
            }
        },
        error: function(xhr) {
            let errorMessage = '서버 연결에 실패했습니다.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showMessage(errorMessage, 'error');
        }
    });
}

function openEditPrepaidExpenseModal(expenseId) {
    // API에서 선납지출 정보 가져오기
    $.ajax({
        url: getAPIUrl('prepaid-expenses') + '/' + expenseId,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const expense = response.data;

                // 폼에 데이터 채우기
                $('#edit-prepaid-expense-id').val(expense.id);
                $('#edit-prepaid-item-name').val(expense.item_name);
                $('#edit-prepaid-amount').val(expense.amount);
                $('#edit-prepaid-payment-date').val(expense.payment_date);
                $('#edit-prepaid-payment-method').val(expense.payment_method);

                // 라벨 업데이트
                M.updateTextFields();
                M.FormSelect.init(document.querySelectorAll('select:not(.browser-default)'));

                // 모달 열기
                M.Modal.getInstance(document.getElementById('edit-prepaid-expense-modal')).open();
            } else {
                showMessage('선납지출 정보를 불러올 수 없습니다.', 'error');
            }
        },
        error: function() {
            showMessage('서버 연결에 실패했습니다.', 'error');
        }
    });
}

function saveEditedPrepaidExpense() {
    const expenseId = $('#edit-prepaid-expense-id').val();
    const itemName = $('#edit-prepaid-item-name').val().trim();
    const amount = $('#edit-prepaid-amount').val();
    const paymentDate = $('#edit-prepaid-payment-date').val();
    const paymentMethod = $('#edit-prepaid-payment-method').val();

    // 유효성 검사
    if (!itemName) {
        showMessage('항목명을 입력해주세요.', 'error');
        $('#edit-prepaid-item-name').focus();
        return;
    }

    if (!amount || amount <= 0) {
        showMessage('올바른 금액을 입력해주세요.', 'error');
        $('#edit-prepaid-amount').focus();
        return;
    }

    if (!paymentDate || paymentDate < 1 || paymentDate > 31) {
        showMessage('올바른 결제일을 입력해주세요.', 'error');
        $('#edit-prepaid-payment-date').focus();
        return;
    }

    if (!paymentMethod) {
        showMessage('결제수단을 선택해주세요.', 'error');
        return;
    }

    // API 요청 데이터 준비
    const data = {
        item_name: itemName,
        amount: parseInt(amount),
        payment_date: parseInt(paymentDate),
        payment_method: paymentMethod
    };

    // API 호출
    $.ajax({
        url: getAPIUrl('prepaid-expenses') + '/' + expenseId,
        type: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            if (response.success) {
                showMessage('선납지출이 수정되었습니다.', 'success');
                M.Modal.getInstance(document.getElementById('edit-prepaid-expense-modal')).close();

                setTimeout(function() {
                    loadPrepaidExpenses();
                }, 500);
            } else {
                showMessage(response.message || '선납지출 수정에 실패했습니다.', 'error');
            }
        },
        error: function(xhr) {
            let errorMessage = '서버 연결에 실패했습니다.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showMessage(errorMessage, 'error');
        }
    });
}

function deletePrepaidExpense() {
    const expenseId = $('#edit-prepaid-expense-id').val();

    if (!expenseId) {
        showMessage('삭제할 선납지출을 선택해주세요.', 'error');
        return;
    }

    if (!confirm('정말로 이 선납지출을 삭제하시겠습니까?')) {
        return;
    }

    $.ajax({
        url: getAPIUrl('prepaid-expenses') + '/' + expenseId,
        type: 'DELETE',
        success: function(response) {
            if (response.success) {
                showMessage('선납지출이 삭제되었습니다.', 'success');
                M.Modal.getInstance(document.getElementById('edit-prepaid-expense-modal')).close();

                setTimeout(function() {
                    loadPrepaidExpenses();
                }, 500);
            } else {
                showMessage(response.message || '선납지출 삭제에 실패했습니다.', 'error');
            }
        },
        error: function(xhr) {
            let errorMessage = '서버 연결에 실패했습니다.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showMessage(errorMessage, 'error');
        }
    });
}

function clearPrepaidExpenseForm() {
    $('#add-prepaid-expense-form')[0].reset();
    M.FormSelect.init(document.querySelectorAll('select:not(.browser-default)'));
    M.updateTextFields();
}

function updateMonthlyExpensesTotal() {
    // 고정지출과 선납지출 총액 계산
    const fixedExpensesText = $('#fixed-expenses-total').text();
    const prepaidExpensesText = $('#prepaid-expenses-total').text();

    // 텍스트에서 숫자 추출 (₩ 및 쉼표 제거)
    const fixedAmount = parseInt(fixedExpensesText.replace(/[₩,]/g, '') || '0');
    const prepaidAmount = parseInt(prepaidExpensesText.replace(/[₩,]/g, '') || '0');

    const totalAmount = fixedAmount + prepaidAmount;
    $('#total-monthly-expenses').text('₩' + totalAmount.toLocaleString());
}

function showMessage(text, type) {
    let colorClass = 'blue';
    if (type === 'error') colorClass = 'red';
    if (type === 'success') colorClass = 'green';

    M.toast({
        html: text,
        classes: colorClass + ' white-text',
        displayLength: 3000
    });
}
</script>

<?php include 'includes/footer.php'; ?>