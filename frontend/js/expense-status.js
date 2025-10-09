// API Base URL (프로덕션: /api, 개발: http://localhost:8080/api)
const API_BASE_URL = window.location.hostname === 'localhost' ? 'http://localhost:8080/api' : '/api';

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

function getAPIUrl(endpoint, id = null) {
    if (currentViewMode === 'current') {
        // 현재 모드: /api/fixed-expenses 또는 /api/fixed-expenses/123
        return id ? `${API_BASE_URL}/${endpoint}/${id}` : `${API_BASE_URL}/${endpoint}`;
    } else {
        // 아카이브 모드
        if (id) {
            // PUT/PATCH/DELETE: /api/expense-archive/fixed-expenses/123
            return `${API_BASE_URL}/expense-archive/${endpoint}/${id}`;
        } else {
            // GET: /api/expense-archive/fixed-expenses?year=2024&month=9
            const [year, monthNum] = currentSelectedMonth.split('-');
            return `${API_BASE_URL}/expense-archive/${endpoint}?year=${year}&month=${parseInt(monthNum)}`;
        }
    }
}

function initMonthSelector() {
    loadAvailableArchiveMonths();
}

function loadAvailableArchiveMonths() {
    $.ajax({
        url: `${API_BASE_URL}/expense-archive/available-months`,
        type: 'GET',
        xhrFields: {
            withCredentials: true
        },
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
        url = `${API_BASE_URL}/fixed-expenses`;
    } else {
        const [year, monthNum] = currentSelectedMonth.split('-');
        url = `${API_BASE_URL}/expense-archive/fixed-expenses?year=${year}&month=${parseInt(monthNum)}`;
    }

    $.ajax({
        url: url,
        type: 'GET',
        xhrFields: {
            withCredentials: true
        },
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

        // 테이블 행 추가 (인라인 편집 기능 포함)
        let row = '<tr class="expense-row" data-id="' + expense.id + '" data-expense-type="fixed" style="cursor: pointer;">' +
                  '<td style="color: #424242 !important;">' + (expense.item_name || '-') + '</td>' +
                  '<td class="amount-cell editable negative" style="font-weight: bold; cursor: pointer;">₩' + amount.toLocaleString() + '</td>' +
                  '<td style="color: #424242 !important;">' + paymentDate + '</td>' +
                  '<td style="color: #424242 !important;">' + (expense.payment_method || '-') + '</td>' +
                  '</tr>';
        tbody.append(row);

        // 모바일 카드 추가 (인라인 편집 기능 포함)
        let card = '<div class="expense-card" data-id="' + expense.id + '" data-expense-type="fixed" style="margin-bottom: 10px; border-left: 4px solid #f44336; cursor: pointer;">' +
                   '<div class="card-content" style="padding: 12px;">' +
                       '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">' +
                           '<span style="font-weight: bold; color: #424242;">' + (expense.item_name || '-') + '</span>' +
                           '<span class="amount-cell editable" style="font-weight: bold; color: #f44336; cursor: pointer; padding: 8px; border-radius: 4px;">₩' + amount.toLocaleString() + '</span>' +
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

    // 드래그앤드롭 초기화 (데이터 로드 후)
    setTimeout(initializeSortable, 100);
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

    // 로딩 시작
    const loadingId = 'save-fixed-expense';
    if (typeof Feedback !== 'undefined') {
        Feedback.showLoading(loadingId, '고정지출 저장 중...');
    }

    // API 호출
    $.ajax({
        url: getAPIUrl('fixed-expenses'),
        type: 'POST',
        xhrFields: {
            withCredentials: true
        },
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            if (typeof Feedback !== 'undefined') {
                Feedback.hideLoading(loadingId);
            }

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
            if (typeof Feedback !== 'undefined') {
                Feedback.hideLoading(loadingId);
            }

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
        url: getAPIUrl('fixed-expenses', expenseId),
        type: 'GET',
        xhrFields: {
            withCredentials: true
        },
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

    // 로딩 시작
    const loadingId = 'update-fixed-expense';
    if (typeof Feedback !== 'undefined') {
        Feedback.showLoading(loadingId, '고정지출 수정 중...');
    }

    // API 호출
    $.ajax({
        url: getAPIUrl('fixed-expenses', expenseId),
        type: 'PUT',
        xhrFields: {
            withCredentials: true
        },
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            if (typeof Feedback !== 'undefined') {
                Feedback.hideLoading(loadingId);
            }

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
            if (typeof Feedback !== 'undefined') {
                Feedback.hideLoading(loadingId);
            }

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

    // 확인 다이얼로그
    if (typeof Feedback !== 'undefined') {
        Feedback.confirm('정말로 이 고정지출을 삭제하시겠습니까?', function() {
            deleteFixedExpenseConfirmed(expenseId);
        });
    } else {
        if (!confirm('정말로 이 고정지출을 삭제하시겠습니까?')) {
            return;
        }
        deleteFixedExpenseConfirmed(expenseId);
    }
}

function deleteFixedExpenseConfirmed(expenseId) {
    // 로딩 시작
    const loadingId = 'delete-fixed-expense';
    if (typeof Feedback !== 'undefined') {
        Feedback.showLoading(loadingId, '고정지출 삭제 중...');
    }

    $.ajax({
        url: getAPIUrl('fixed-expenses', expenseId),
        type: 'DELETE',
        xhrFields: {
            withCredentials: true
        },
        success: function(response) {
            if (typeof Feedback !== 'undefined') {
                Feedback.hideLoading(loadingId);
            }

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
            if (typeof Feedback !== 'undefined') {
                Feedback.hideLoading(loadingId);
            }

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
        url = `${API_BASE_URL}/prepaid-expenses`;
    } else {
        const [year, monthNum] = currentSelectedMonth.split('-');
        url = `${API_BASE_URL}/expense-archive/prepaid-expenses?year=${year}&month=${parseInt(monthNum)}`;
    }

    $.ajax({
        url: url,
        type: 'GET',
        xhrFields: {
            withCredentials: true
        },
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

        // 테이블 행 추가 (인라인 편집 기능 포함)
        let row = '<tr class="prepaid-expense-row" data-id="' + expense.id + '" data-expense-type="prepaid" style="cursor: pointer;">' +
                  '<td style="color: #424242 !important;">' + (expense.item_name || '-') + '</td>' +
                  '<td class="amount-cell editable negative" style="font-weight: bold; cursor: pointer;">₩' + amount.toLocaleString() + '</td>' +
                  '<td style="color: #424242 !important;">' + paymentDate + '</td>' +
                  '<td style="color: #424242 !important;">' + (expense.payment_method || '-') + '</td>' +
                  '</tr>';
        tbody.append(row);

        // 모바일 카드 추가 (인라인 편집 기능 포함)
        let card = '<div class="prepaid-expense-card" data-id="' + expense.id + '" data-expense-type="prepaid" style="margin-bottom: 10px; border-left: 4px solid #2196F3; cursor: pointer;">' +
                   '<div class="card-content" style="padding: 12px;">' +
                       '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">' +
                           '<span style="font-weight: bold; color: #424242;">' + (expense.item_name || '-') + '</span>' +
                           '<span class="amount-cell editable" style="font-weight: bold; color: #2196F3; cursor: pointer; padding: 8px; border-radius: 4px;">₩' + amount.toLocaleString() + '</span>' +
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

    // 드래그앤드롭 초기화 (데이터 로드 후)
    setTimeout(initializeSortable, 100);
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
        xhrFields: {
            withCredentials: true
        },
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
        url: getAPIUrl('prepaid-expenses', expenseId),
        type: 'GET',
        xhrFields: {
            withCredentials: true
        },
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
        url: getAPIUrl('prepaid-expenses', expenseId),
        type: 'PUT',
        xhrFields: {
            withCredentials: true
        },
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
        url: getAPIUrl('prepaid-expenses', expenseId),
        type: 'DELETE',
        xhrFields: {
            withCredentials: true
        },
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

// 피드백 시스템 - feedback.js의 Feedback 객체 사용
function showMessage(text, type) {
    if (typeof Feedback !== 'undefined') {
        Feedback.showMessage(text, type);
    } else {
        // Fallback
        let colorClass = 'blue';
        if (type === 'error') colorClass = 'red';
        if (type === 'success') colorClass = 'green';
        M.toast({
            html: text,
            classes: colorClass + ' white-text',
            displayLength: 3000
        });
    }
}

// ================================
// 인라인 편집 기능 (assets.php에서 가져옴)
// ================================

// 인라인 편집 상태 관리
let isInlineEditing = false;
let currentEditingCell = null;

// 금액 셀 클릭 이벤트 (인라인 편집 시작)
$(document).on('click', '.amount-cell.editable', function(e) {
    e.preventDefault();
    e.stopPropagation();

    if (isInlineEditing) {
        return; // 이미 편집 중이면 무시
    }

    startInlineEdit($(this));
});

function startInlineEdit($cell) {
    if (isInlineEditing) {
        cancelInlineEdit(); // 기존 편집 취소
    }

    isInlineEditing = true;
    currentEditingCell = $cell;

    const currentValue = $cell.text().replace(/[₩,]/g, '');
    const expenseId = $cell.closest('tr, .expense-card').data('id');
    const isFixed = $cell.closest('[data-expense-type]').data('expense-type') === 'fixed';

    // 셀 내용을 입력 필드로 교체
    $cell.html(`
        <input type="number" class="inline-edit-input" value="${currentValue}" data-original="${currentValue}">
        <div class="inline-edit-actions">
            <button class="btn waves-effect waves-light green inline-edit-btn inline-edit-save" type="button">
                <i class="material-icons left">check</i>저장
            </button>
            <button class="btn waves-effect waves-light red inline-edit-btn inline-edit-cancel" type="button">
                <i class="material-icons left">close</i>취소
            </button>
        </div>
    `);

    // 입력 필드에 포커스
    const $input = $cell.find('.inline-edit-input');
    $input.focus().select();

    // Enter 키로 저장, Esc 키로 취소
    $input.on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            saveInlineEdit($cell, expenseId, isFixed);
        } else if (e.key === 'Escape') {
            e.preventDefault();
            cancelInlineEdit();
        }
    });

    // 저장 버튼 클릭
    $cell.find('.inline-edit-save').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        saveInlineEdit($cell, expenseId, isFixed);
    });

    // 취소 버튼 클릭
    $cell.find('.inline-edit-cancel').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        cancelInlineEdit();
    });
}

function saveInlineEdit($cell, expenseId, isFixed) {
    const $input = $cell.find('.inline-edit-input');
    const newValue = $input.val();
    const originalValue = $input.data('original');

    if (newValue === originalValue) {
        cancelInlineEdit(); // 값이 변경되지 않았으면 취소
        return;
    }

    if (!newValue || isNaN(newValue) || parseFloat(newValue) < 0) {
        showMessage('올바른 금액을 입력해주세요.', 'error');
        $input.focus().select();
        return;
    }

    // 로딩 상태 표시
    $cell.html('<div class="preloader-wrapper small active"><div class="spinner-layer spinner-blue-only"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div>');

    // API 호출
    const endpoint = isFixed ? 'fixed-expenses' : 'prepaid-expenses';
    const data = {
        amount: parseFloat(newValue)
    };

    $.ajax({
        url: getAPIUrl(endpoint, expenseId),
        type: 'PATCH',
        data: JSON.stringify(data),
        contentType: 'application/json',
        xhrFields: {
            withCredentials: true
        },
        success: function(response) {
            if (response.success) {
                // 성공시 새 값으로 업데이트
                $cell.html('₩' + parseInt(newValue).toLocaleString());
                showMessage('금액이 성공적으로 수정되었습니다.', 'success');

                // 총액 업데이트
                if (isFixed) {
                    loadFixedExpenses();
                } else {
                    loadPrepaidExpenses();
                }
            } else {
                // 실패시 원래 값으로 복구
                $cell.html('₩' + parseInt(originalValue).toLocaleString());
                showMessage(response.message || '수정에 실패했습니다.', 'error');
            }

            isInlineEditing = false;
            currentEditingCell = null;
        },
        error: function(xhr) {
            // 에러시 원래 값으로 복구
            $cell.html('₩' + parseInt(originalValue).toLocaleString());

            let errorMessage = '서버 연결에 실패했습니다.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showMessage(errorMessage, 'error');

            isInlineEditing = false;
            currentEditingCell = null;
        }
    });
}

function cancelInlineEdit() {
    if (!isInlineEditing || !currentEditingCell) {
        return;
    }

    const $input = currentEditingCell.find('.inline-edit-input');
    const originalValue = $input.data('original');

    // 원래 값으로 복구
    currentEditingCell.html('₩' + parseInt(originalValue).toLocaleString());

    isInlineEditing = false;
    currentEditingCell = null;
}

// 다른 곳 클릭시 편집 취소
$(document).on('click', function(e) {
    if (isInlineEditing && !$(e.target).closest('.amount-cell').length) {
        cancelInlineEdit();
    }
});

// ================================
// 드래그 앤 드롭 기능 (assets.php에서 가져옴)
// ================================

let fixedExpensesSortable = null;
let prepaidExpensesSortable = null;
let fixedExpensesCardsSortable = null;
let prepaidExpensesCardsSortable = null;

function initializeSortable() {
    // 기존 Sortable 인스턴스 제거
    destroySortableInstances();

    // 고정지출 테이블 드래그앤드롭 (jQuery UI sortable 사용)
    const $fixedExpensesTable = $('#fixed-expenses-table');
    if ($fixedExpensesTable.length) {
        $fixedExpensesTable.sortable({
            items: 'tr:not(.no-drag)',
            handle: 'tr',
            placeholder: 'sortable-placeholder',
            update: function(event, ui) {
                const oldIndex = ui.item.data('old-index');
                const newIndex = ui.item.index();
                if (oldIndex !== newIndex) {
                    updateExpenseOrder('fixed-expenses', oldIndex, newIndex);
                }
            },
            start: function(event, ui) {
                ui.item.data('old-index', ui.item.index());
            }
        });
        fixedExpensesSortable = $fixedExpensesTable;
    }

    // 선납지출 테이블 드래그앤드롭 (jQuery UI sortable 사용)
    const $prepaidExpensesTable = $('#prepaid-expenses-table');
    if ($prepaidExpensesTable.length) {
        $prepaidExpensesTable.sortable({
            items: 'tr:not(.no-drag)',
            handle: 'tr',
            placeholder: 'sortable-placeholder',
            update: function(event, ui) {
                const oldIndex = ui.item.data('old-index');
                const newIndex = ui.item.index();
                if (oldIndex !== newIndex) {
                    updateExpenseOrder('prepaid-expenses', oldIndex, newIndex);
                }
            },
            start: function(event, ui) {
                ui.item.data('old-index', ui.item.index());
            }
        });
        prepaidExpensesSortable = $prepaidExpensesTable;
    }

    // 고정지출 모바일 카드 드래그앤드롭 (jQuery UI sortable 사용)
    const $fixedExpensesCards = $('#fixed-expenses-cards');
    if ($fixedExpensesCards.length) {
        $fixedExpensesCards.sortable({
            items: '.expense-card:not(.no-drag)',
            handle: '.expense-card',
            placeholder: 'sortable-placeholder',
            update: function(event, ui) {
                const oldIndex = ui.item.data('old-index');
                const newIndex = ui.item.index();
                if (oldIndex !== newIndex) {
                    updateExpenseOrder('fixed-expenses', oldIndex, newIndex);
                }
            },
            start: function(event, ui) {
                ui.item.data('old-index', ui.item.index());
            }
        });
        fixedExpensesCardsSortable = $fixedExpensesCards;
    }

    // 선납지출 모바일 카드 드래그앤드롭 (jQuery UI sortable 사용)
    const $prepaidExpensesCards = $('#prepaid-expenses-cards');
    if ($prepaidExpensesCards.length) {
        $prepaidExpensesCards.sortable({
            items: '.prepaid-expense-card:not(.no-drag)',
            handle: '.prepaid-expense-card',
            placeholder: 'sortable-placeholder',
            update: function(event, ui) {
                const oldIndex = ui.item.data('old-index');
                const newIndex = ui.item.index();
                if (oldIndex !== newIndex) {
                    updateExpenseOrder('prepaid-expenses', oldIndex, newIndex);
                }
            },
            start: function(event, ui) {
                ui.item.data('old-index', ui.item.index());
            }
        });
        prepaidExpensesCardsSortable = $prepaidExpensesCards;
    }
}

function destroySortableInstances() {
    if (fixedExpensesSortable && fixedExpensesSortable.sortable) {
        fixedExpensesSortable.sortable('destroy');
        fixedExpensesSortable = null;
    }
    if (prepaidExpensesSortable && prepaidExpensesSortable.sortable) {
        prepaidExpensesSortable.sortable('destroy');
        prepaidExpensesSortable = null;
    }
    if (fixedExpensesCardsSortable && fixedExpensesCardsSortable.sortable) {
        fixedExpensesCardsSortable.sortable('destroy');
        fixedExpensesCardsSortable = null;
    }
    if (prepaidExpensesCardsSortable && prepaidExpensesCardsSortable.sortable) {
        prepaidExpensesCardsSortable.sortable('destroy');
        prepaidExpensesCardsSortable = null;
    }
}

function updateExpenseOrder(type, oldIndex, newIndex) {
    // 현재 표시된 지출 목록에서 ID 순서 추출
    let expenseIds = [];
    if (type === 'fixed-expenses') {
        $('#fixed-expenses-table tr[data-id], #fixed-expenses-cards .expense-card[data-id]').each(function() {
            const id = $(this).data('id');
            if (id) expenseIds.push(id);
        });
    } else {
        $('#prepaid-expenses-table tr[data-id], #prepaid-expenses-cards .prepaid-expense-card[data-id]').each(function() {
            const id = $(this).data('id');
            if (id) expenseIds.push(id);
        });
    }

    // 중복 제거 (테이블과 카드에서 같은 ID가 나올 수 있음)
    expenseIds = [...new Set(expenseIds)];

    if (expenseIds.length === 0) {
        return;
    }

    // API 호출
    $.ajax({
        url: getAPIUrl(type) + '/reorder',
        type: 'PUT',
        data: JSON.stringify({ order: expenseIds }),
        contentType: 'application/json',
        xhrFields: {
            withCredentials: true
        },
        success: function(response) {
            if (response.success) {
                showMessage('순서가 변경되었습니다.', 'success');
            } else {
                showMessage(response.message || '순서 변경에 실패했습니다.', 'error');
                // 실패시 원래 순서로 복원
                if (type === 'fixed-expenses') {
                    loadFixedExpenses();
                } else {
                    loadPrepaidExpenses();
                }
            }
        },
        error: function(xhr) {
            let errorMessage = '서버 연결에 실패했습니다.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showMessage(errorMessage, 'error');

            // 실패시 원래 순서로 복원
            if (type === 'fixed-expenses') {
                loadFixedExpenses();
            } else {
                loadPrepaidExpenses();
            }
        }
    });
}
