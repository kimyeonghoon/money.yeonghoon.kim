// API Base URL (프로덕션: /api, 개발: ' + API_BASE_URL + ')
const API_BASE_URL = window.location.hostname === 'localhost' ? '' + API_BASE_URL + '' : '/api';

/**
 * 일간지출내역 페이지 JavaScript
 */

// KST(한국 표준시) 기준 날짜 문자열 반환 (YYYY-MM-DD)
function getTodayKST() {
    const now = new Date();
    // KST = UTC+9
    const kstOffset = 9 * 60; // 분 단위
    const kstTime = new Date(now.getTime() + (kstOffset + now.getTimezoneOffset()) * 60000);
    return kstTime.toISOString().split('T')[0];
}

// KST 기준 Date 객체 반환
function getDateKST(dateString) {
    if (!dateString) {
        const now = new Date();
        const kstOffset = 9 * 60;
        return new Date(now.getTime() + (kstOffset + now.getTimezoneOffset()) * 60000);
    }
    return new Date(dateString + 'T00:00:00+09:00');
}

// 달력 관련 변수
let currentCalendarYear = getDateKST().getFullYear();
let currentCalendarMonth = getDateKST().getMonth(); // 0-based (0=January)
let monthlyExpensesData = {};

$(document).ready(function() {
    // 모달 초기화
    M.Modal.init(document.querySelectorAll('.modal'));

    loadDailyExpenses();
    updateExpenseStatistics();
    ensureMissingExpensesExist(); // 누락된 날짜들의 지출 기록 생성

    // 달력 초기화
    initializeCalendar();
    loadMonthlyExpenses();

    // 카테고리별 금액 입력시 총액 자동 계산
    $('#edit-food-cost, #edit-necessities-cost, #edit-transportation-cost, #edit-other-cost').on('input', function() {
        calculateEditTotalAmount();
    });

    // 지출 추가 버튼 이벤트 핸들러
    $('#add-expense-btn').on('click', function() {
        openAddExpenseModal();
    });

    // 지출 추가 저장 버튼 이벤트 핸들러
    $('#save-add-expense').on('click', function() {
        saveAddedExpense();
    });

    // 지출 추가 입력시 총액 미리보기
    $('#add-food-cost, #add-necessities-cost, #add-transportation-cost, #add-other-cost').on('input', function() {
        updateAddTotalPreview();
    });

    // 일간지출 수정 버튼 이벤트 핸들러
    $('#save-daily-expense-edit').on('click', function() {
        saveEditedDailyExpense();
    });

    // 테이블 행 더블클릭 이벤트
    $(document).on('dblclick', '.daily-expense-row', function() {
        const expenseId = $(this).data('id');
        openEditDailyExpenseModal(expenseId);
    });

    // 모바일 카드 길게 터치 이벤트
    let touchTimer;
    $(document).on('touchstart', '.daily-expense-card', function(e) {
        const expenseId = $(this).data('id');
        touchTimer = setTimeout(function() {
            openEditDailyExpenseModal(expenseId);
        }, 800); // 800ms 길게 터치
    });

    $(document).on('touchend touchmove', '.daily-expense-card', function() {
        clearTimeout(touchTimer);
    });

    // 모바일 카드 더블 탭 이벤트 (대안)
    $(document).on('dblclick', '.daily-expense-card', function() {
        const expenseId = $(this).data('id');
        openEditDailyExpenseModal(expenseId);
    });

    // 달력 월 이동 이벤트
    $('#prev-month-btn').on('click', function() {
        changeMonth(-1);
    });

    $('#next-month-btn').on('click', function() {
        changeMonth(1);
    });

    // 달력 날짜 클릭 이벤트
    $(document).on('click', '.calendar-day', function() {
        const date = $(this).data('date');
        if (date && !$(this).hasClass('other-month')) {
            // 해당 날짜의 지출 편집 모달 열기
            openEditDailyExpenseByDate(date);
        }
    });

    // 달력 날짜 더블클릭 이벤트 (인라인 편집)
    $(document).on('dblclick', '.calendar-day:not(.other-month)', function(e) {
        e.stopPropagation();
        const date = $(this).data('date');
        const $day = $(this);

        if (!$day.hasClass('editing')) {
            openInlineCalendarEdit($day, date);
        }
    });

    // 달력 날짜 길게 터치 이벤트 (모바일)
    let calendarTouchTimer;
    $(document).on('touchstart', '.calendar-day:not(.other-month)', function(e) {
        const $day = $(this);
        const date = $day.data('date');

        calendarTouchTimer = setTimeout(function() {
            if (!$day.hasClass('editing')) {
                openInlineCalendarEdit($day, date);
            }
        }, 600); // 600ms 길게 터치
    });

    $(document).on('touchend touchmove', '.calendar-day', function() {
        clearTimeout(calendarTouchTimer);
    });
});

function loadDailyExpenses() {
    $.ajax({
        url: '' + API_BASE_URL + '/daily-expenses',
        type: 'GET',
        xhrFields: {
            withCredentials: true
        },
        success: function(response) {
            if (response.success) {
                displayDailyExpenses(response.data);
            } else {
                showMessage('일간지출 데이터를 불러올 수 없습니다.', 'error');
            }
        },
        error: function() {
            showMessage('서버 연결에 실패했습니다.', 'error');
        }
    });
}

function displayDailyExpenses(expenses) {
    let tbody = $('#daily-expenses-table');
    let cardsContainer = $('#daily-expenses-cards');

    tbody.empty();
    cardsContainer.empty();

    if (!expenses || expenses.length === 0) {
        tbody.append('<tr><td colspan="6" class="center-align">일간지출 기록이 없습니다.</td></tr>');
        cardsContainer.append('<div class="center-align">일간지출 기록이 없습니다.</div>');
        return;
    }

    // 날짜 기준으로 내림차순 정렬 (최신순)
    expenses.sort((a, b) => new Date(b.expense_date) - new Date(a.expense_date));

    expenses.forEach(function(expense) {
        const expenseDate = new Date(expense.expense_date).toLocaleDateString('ko-KR');
        const totalAmount = parseInt(expense.total_amount || 0);
        const foodCost = parseInt(expense.food_cost || 0);
        const necessitiesCost = parseInt(expense.necessities_cost || 0);
        const transportationCost = parseInt(expense.transportation_cost || 0);
        const otherCost = parseInt(expense.other_cost || 0);

        // 테이블 행 추가
        let row = '<tr class="daily-expense-row" data-id="' + expense.id + '" style="cursor: pointer;">' +
                  '<td style="color: #424242 !important;">' + expenseDate + '</td>' +
                  '<td class="negative" style="font-weight: bold;">₩' + totalAmount.toLocaleString() + '</td>' +
                  '<td style="color: #424242 !important;">₩' + foodCost.toLocaleString() + '</td>' +
                  '<td style="color: #424242 !important;">₩' + necessitiesCost.toLocaleString() + '</td>' +
                  '<td style="color: #424242 !important;">₩' + transportationCost.toLocaleString() + '</td>' +
                  '<td style="color: #424242 !important;">₩' + otherCost.toLocaleString() + '</td>' +
                  '</tr>';
        tbody.append(row);

        // 모바일 카드 추가
        let card = '<div class="daily-expense-card" data-id="' + expense.id + '" style="margin-bottom: 10px; border-left: 4px solid #FF5722; cursor: pointer;">' +
                   '<div class="card-content" style="padding: 12px;">' +
                       '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">' +
                           '<span style="font-weight: bold; color: #424242;">' + expenseDate + '</span>' +
                           '<span style="font-weight: bold; color: #FF5722;">₩' + totalAmount.toLocaleString() + '</span>' +
                       '</div>' +
                       '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 5px; font-size: 14px; color: #666;">' +
                           '<span>🍽️ ₩' + foodCost.toLocaleString() + '</span>' +
                           '<span>🛒 ₩' + necessitiesCost.toLocaleString() + '</span>' +
                           '<span>🚌 ₩' + transportationCost.toLocaleString() + '</span>' +
                           '<span>💰 ₩' + otherCost.toLocaleString() + '</span>' +
                       '</div>' +
                   '</div>' +
                   '</div>';
        cardsContainer.append(card);
    });
}

function ensureMissingExpensesExist() {
    // 기존 데이터를 먼저 로드하여 누락된 날짜를 확인
    $.ajax({
        url: '' + API_BASE_URL + '/daily-expenses',
        type: 'GET',
        xhrFields: {
            withCredentials: true
        },
        success: function(response) {
            if (response.success) {
                const existingDates = response.data.map(expense => expense.expense_date);
                const today = new Date();
                const missingDates = [];

                // 지난 7일간 확인
                for (let i = 6; i >= 0; i--) {
                    const date = new Date(today);
                    date.setDate(today.getDate() - i);
                    const dateStr = date.toISOString().split('T')[0];

                    if (!existingDates.includes(dateStr)) {
                        missingDates.push(dateStr);
                    }
                }

                if (missingDates.length > 0) {
                    createMissingExpenses(missingDates);
                } else {
                    console.log('누락된 지출 기록이 없습니다.');
                }
            }
        },
        error: function() {
            console.log('기존 지출 기록 조회 실패');
        }
    });
}

function createMissingExpenses(dates) {
    let createdCount = 0;
    let processedCount = 0;

    dates.forEach(function(date) {
        $.ajax({
            url: '' + API_BASE_URL + '/daily-expenses',
            type: 'POST',
        xhrFields: {
            withCredentials: true
        },
            contentType: 'application/json',
            data: JSON.stringify({
                expense_date: date,
                total_amount: 0,
                food_cost: 0,
                necessities_cost: 0,
                transportation_cost: 0,
                other_cost: 0
            }),
            success: function(response) {
                processedCount++;
                if (response.success) {
                    createdCount++;
                    console.log(`${date} 지출 기록 생성 완료`);
                }

                if (processedCount === dates.length) {
                    finalizeMissingExpensesCreation(createdCount);
                }
            },
            error: function(xhr) {
                processedCount++;
                console.log(`${date} 지출 기록 생성 실패:`, xhr.responseJSON);

                if (processedCount === dates.length) {
                    finalizeMissingExpensesCreation(createdCount);
                }
            }
        });
    });
}

function finalizeMissingExpensesCreation(createdCount) {
    if (createdCount > 0) {
        console.log(`누락된 지출 기록 ${createdCount}개 생성 완료`);
        showMessage(`누락된 ${createdCount}개 날짜의 지출 기록을 생성했습니다.`, 'info');
    }

    // 생성 후 데이터 다시 로드
    setTimeout(function() {
        loadDailyExpenses();
    }, 500);
}

function calculateEditTotalAmount() {
    const foodCost = parseInt($('#edit-food-cost').val() || 0);
    const necessitiesCost = parseInt($('#edit-necessities-cost').val() || 0);
    const transportationCost = parseInt($('#edit-transportation-cost').val() || 0);
    const otherCost = parseInt($('#edit-other-cost').val() || 0);

    const totalAmount = foodCost + necessitiesCost + transportationCost + otherCost;
    $('#edit-total-amount').val(totalAmount);

    // 라벨 업데이트
    M.updateTextFields();
}

function updateExpenseStatistics() {
    const today = getDateKST();
    const todayStr = getTodayKST();

    // 이번 주 시작일 계산 (월요일)
    const startOfWeek = new Date(today);
    startOfWeek.setDate(today.getDate() - today.getDay() + 1);
    const year = startOfWeek.getFullYear();
    const month = String(startOfWeek.getMonth() + 1).padStart(2, '0');
    const day = String(startOfWeek.getDate()).padStart(2, '0');
    const weekStartStr = `${year}-${month}-${day}`;

    // 이번 달 시작일
    const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    const monthStartStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-01`;

    // 통계 API 호출
    $.ajax({
        url: '' + API_BASE_URL + '/daily-expenses/statistics',
        type: 'GET',
        xhrFields: {
            withCredentials: true
        },
        data: {
            today: todayStr,
            week_start: weekStartStr,
            month_start: monthStartStr
        },
        success: function(response) {
            if (response.success) {
                const stats = response.data;
                $('#today-expenses-total').text('₩' + (stats.today || 0).toLocaleString());
                $('#week-expenses-total').text('₩' + (stats.week || 0).toLocaleString());
                $('#month-expenses-total').text('₩' + (stats.month || 0).toLocaleString());
            }
        },
        error: function() {
            // 통계 로드 실패시 기본값 유지
            console.log('통계 데이터 로드 실패');
        }
    });
}

function openEditDailyExpenseModal(expenseId) {
    // API에서 일간지출 정보 가져오기
    $.ajax({
        url: '' + API_BASE_URL + '/daily-expenses/' + expenseId,
        type: 'GET',
        xhrFields: {
            withCredentials: true
        },
        success: function(response) {
            if (response.success) {
                const expense = response.data;

                // 폼에 데이터 채우기
                $('#edit-daily-expense-id').val(expense.id);
                $('#edit-expense-date').val(expense.expense_date);
                $('#edit-total-amount').val(expense.total_amount);
                $('#edit-food-cost').val(expense.food_cost || '');
                $('#edit-necessities-cost').val(expense.necessities_cost || '');
                $('#edit-transportation-cost').val(expense.transportation_cost || '');
                $('#edit-other-cost').val(expense.other_cost || '');

                // 라벨 업데이트
                M.updateTextFields();

                // 총액 자동 계산
                calculateEditTotalAmount();

                // 모달 열기
                M.Modal.getInstance(document.getElementById('edit-daily-expense-modal')).open();
            } else {
                showMessage('일간지출 정보를 불러올 수 없습니다.', 'error');
            }
        },
        error: function() {
            showMessage('서버 연결에 실패했습니다.', 'error');
        }
    });
}

function saveEditedDailyExpense() {
    const expenseId = $('#edit-daily-expense-id').val();
    const expenseDate = $('#edit-expense-date').val();
    const totalAmount = $('#edit-total-amount').val();
    const foodCost = $('#edit-food-cost').val() || 0;
    const necessitiesCost = $('#edit-necessities-cost').val() || 0;
    const transportationCost = $('#edit-transportation-cost').val() || 0;
    const otherCost = $('#edit-other-cost').val() || 0;

    // API 요청 데이터 준비
    const data = {
        expense_date: expenseDate,
        total_amount: parseInt(totalAmount),
        food_cost: parseInt(foodCost),
        necessities_cost: parseInt(necessitiesCost),
        transportation_cost: parseInt(transportationCost),
        other_cost: parseInt(otherCost)
    };

    // 로딩 시작
    const loadingId = 'save-daily-expense';
    if (typeof Feedback !== 'undefined') {
        Feedback.showLoading(loadingId, '일간지출 저장 중...');
    }

    // API 호출
    $.ajax({
        url: '' + API_BASE_URL + '/daily-expenses/' + expenseId,
        type: 'PUT',
        timeout: 10000,  // 10초 타임아웃
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
                showMessage('일간지출이 저장되었습니다.', 'success');
                M.Modal.getInstance(document.getElementById('edit-daily-expense-modal')).close();

                setTimeout(function() {
                    loadDailyExpenses();
                    updateExpenseStatistics();
                    loadMonthlyExpenses();
                }, 500);
            } else {
                showMessage(response.message || '일간지출 저장에 실패했습니다.', 'error');
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

function openAddExpenseModal() {
    // 폼 초기화
    $('#add-food-cost').val(0);
    $('#add-necessities-cost').val(0);
    $('#add-transportation-cost').val(0);
    $('#add-other-cost').val(0);

    // 라벨 업데이트
    M.updateTextFields();

    // 총액 미리보기 업데이트
    updateAddTotalPreview();

    // 모달 열기
    M.Modal.getInstance(document.getElementById('add-expense-modal')).open();
}

function updateAddTotalPreview() {
    const foodCost = parseInt($('#add-food-cost').val() || 0);
    const necessitiesCost = parseInt($('#add-necessities-cost').val() || 0);
    const transportationCost = parseInt($('#add-transportation-cost').val() || 0);
    const otherCost = parseInt($('#add-other-cost').val() || 0);

    const totalAmount = foodCost + necessitiesCost + transportationCost + otherCost;
    $('#add-total-preview').text('₩' + totalAmount.toLocaleString());
}

function saveAddedExpense() {
    const foodCost = parseInt($('#add-food-cost').val() || 0);
    const necessitiesCost = parseInt($('#add-necessities-cost').val() || 0);
    const transportationCost = parseInt($('#add-transportation-cost').val() || 0);
    const otherCost = parseInt($('#add-other-cost').val() || 0);

    const totalAmount = foodCost + necessitiesCost + transportationCost + otherCost;

    if (totalAmount <= 0) {
        showMessage('추가할 금액을 입력해주세요.', 'error');
        return;
    }

    const today = getTodayKST();

    // 로딩 시작
    const loadingId = 'add-daily-expense';
    if (typeof Feedback !== 'undefined') {
        Feedback.showLoading(loadingId, '지출 추가 중...');
    }

    // 오늘 지출 추가 API 호출
    $.ajax({
        url: '' + API_BASE_URL + '/daily-expenses/add-today',
        type: 'POST',
        timeout: 10000,  // 10초 타임아웃
        xhrFields: {
            withCredentials: true
        },
        contentType: 'application/json',
        data: JSON.stringify({
            expense_date: today,
            food_cost: foodCost,
            necessities_cost: necessitiesCost,
            transportation_cost: transportationCost,
            other_cost: otherCost
        }),
        success: function(response) {
            console.log('[DEBUG] add-today API 응답:', response);

            // 로딩 숨기기 (항상 실행)
            if (typeof Feedback !== 'undefined') {
                Feedback.hideLoading(loadingId);
            }

            if (response && response.success) {
                // 성공 메시지
                if (typeof showMessage === 'function') {
                    showMessage('지출이 추가되었습니다.', 'success');
                } else {
                    M.toast({html: '지출이 추가되었습니다.', classes: 'green'});
                }

                // 모달 닫기
                try {
                    const modalEl = document.getElementById('add-expense-modal');
                    if (modalEl) {
                        const modal = M.Modal.getInstance(modalEl);
                        if (modal) {
                            modal.close();
                        }
                    }
                } catch (e) {
                    console.error('[ERROR] 모달 닫기 실패:', e);
                }

                // 데이터 새로고침
                console.log('[DEBUG] 데이터 새로고침 시작');
                setTimeout(function() {
                    try {
                        if (typeof loadDailyExpenses === 'function') loadDailyExpenses();
                        if (typeof updateExpenseStatistics === 'function') updateExpenseStatistics();
                        if (typeof loadMonthlyExpenses === 'function') loadMonthlyExpenses();
                        console.log('[DEBUG] 데이터 새로고침 완료');
                    } catch (e) {
                        console.error('[ERROR] 데이터 새로고침 실패:', e);
                    }
                }, 500);
            } else {
                if (typeof showMessage === 'function') {
                    showMessage(response.message || '지출 추가에 실패했습니다.', 'error');
                } else {
                    M.toast({html: response.message || '지출 추가에 실패했습니다.', classes: 'red'});
                }
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

function initializeCalendar() {
    updateCalendarDisplay();
}

function changeMonth(direction) {
    currentCalendarMonth += direction;

    if (currentCalendarMonth > 11) {
        currentCalendarMonth = 0;
        currentCalendarYear++;
    } else if (currentCalendarMonth < 0) {
        currentCalendarMonth = 11;
        currentCalendarYear--;
    }

    updateCalendarDisplay();
    loadMonthlyExpenses();
}

function updateCalendarDisplay() {
    const monthNames = ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'];
    $('#current-month-display').text(`${currentCalendarYear}년 ${monthNames[currentCalendarMonth]}`);
}

function loadMonthlyExpenses() {
    $.ajax({
        url: '' + API_BASE_URL + '/daily-expenses/by-month',
        type: 'GET',
        xhrFields: {
            withCredentials: true
        },
        data: {
            year: currentCalendarYear,
            month: currentCalendarMonth + 1, // API는 1-based month 사용
            limit: 50
        },
        success: function(response) {
            if (response.success) {
                monthlyExpensesData = {};
                response.data.forEach(function(expense) {
                    monthlyExpensesData[expense.expense_date] = expense;
                });
                renderCalendar();
                updateMonthlyTotal();
            } else {
                showMessage('월간 지출 데이터를 불러올 수 없습니다.', 'error');
            }
        },
        error: function() {
            showMessage('서버 연결에 실패했습니다.', 'error');
        }
    });
}

function renderCalendar() {
    const calendarBody = $('#calendar-body');
    calendarBody.empty();

    const firstDayOfMonth = new Date(currentCalendarYear, currentCalendarMonth, 1);
    const lastDayOfMonth = new Date(currentCalendarYear, currentCalendarMonth + 1, 0);
    const firstDayWeekday = firstDayOfMonth.getDay(); // 0=Sunday
    const daysInMonth = lastDayOfMonth.getDate();

    const todayStr = getTodayKST();

    // 이전 달의 날짜들 (빈 공간 채우기)
    const prevMonth = currentCalendarMonth === 0 ? 11 : currentCalendarMonth - 1;
    const prevYear = currentCalendarMonth === 0 ? currentCalendarYear - 1 : currentCalendarYear;
    const daysInPrevMonth = new Date(prevYear, prevMonth + 1, 0).getDate();

    for (let i = firstDayWeekday - 1; i >= 0; i--) {
        const dayNum = daysInPrevMonth - i;
        const dayElement = $(`
            <div class="calendar-day other-month">
                <div class="calendar-day-number">${dayNum}</div>
            </div>
        `);
        calendarBody.append(dayElement);
    }

    // 현재 달의 날짜들
    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = `${currentCalendarYear}-${String(currentCalendarMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const expense = monthlyExpensesData[dateStr];
        const isToday = dateStr === todayStr;

        let dayClasses = 'calendar-day';
        if (isToday) dayClasses += ' today';
        if (expense && expense.total_amount > 0) dayClasses += ' has-expense';

        let expenseContent = '';
        if (expense && expense.total_amount > 0) {
            const amount = parseInt(expense.total_amount);
            let amountClass = 'low';
            if (amount >= 30000) amountClass = 'high';
            else if (amount >= 10000) amountClass = 'medium';

            expenseContent = `
                <div class="calendar-expense-amount ${amountClass}">
                    ₩${amount.toLocaleString()}
                </div>
                <div class="calendar-expense-detail">
                    🍽️${(expense.food_cost || 0).toLocaleString()}
                    🛒${(expense.necessities_cost || 0).toLocaleString()}
                </div>
            `;
        }

        const dayElement = $(`
            <div class="${dayClasses}" data-date="${dateStr}">
                <div class="calendar-day-number">${day}</div>
                ${expenseContent}
            </div>
        `);

        calendarBody.append(dayElement);
    }

    // 다음 달의 날짜들 (빈 공간 채우기)
    const totalCells = calendarBody.children().length;
    const remainingCells = (Math.ceil(totalCells / 7) * 7) - totalCells;

    for (let day = 1; day <= remainingCells; day++) {
        const dayElement = $(`
            <div class="calendar-day other-month">
                <div class="calendar-day-number">${day}</div>
            </div>
        `);
        calendarBody.append(dayElement);
    }
}

function updateMonthlyTotal() {
    let total = 0;
    Object.values(monthlyExpensesData).forEach(function(expense) {
        total += parseInt(expense.total_amount || 0);
    });
    $('#monthly-total-amount').text('₩' + total.toLocaleString());
}

function openEditDailyExpenseByDate(date) {
    // 해당 날짜의 지출 데이터가 있는지 확인
    $.ajax({
        url: '' + API_BASE_URL + '/daily-expenses/by-date',
        type: 'GET',
        xhrFields: {
            withCredentials: true
        },
        data: { date: date },
        success: function(response) {
            if (response.success && response.data) {
                // 기존 데이터가 있으면 편집 모달 열기
                openEditDailyExpenseModal(response.data.id);
            } else {
                // 데이터가 없으면 해당 날짜로 지출 추가 모달 열기
                openAddExpenseModalForDate(date);
            }
        },
        error: function() {
            // 오류 시 해당 날짜로 지출 추가 모달 열기
            openAddExpenseModalForDate(date);
        }
    });
}

function openAddExpenseModalForDate(date) {
    // 지출 추가 모달을 열고 날짜를 설정
    openAddExpenseModal();
    // 추가: 특정 날짜용 모달로 수정할 수 있지만, 현재는 오늘 지출 추가만 지원
    showMessage(`${date} 날짜의 지출을 추가하려면 "오늘 지출 추가" 기능을 사용하세요.`, 'info');
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
        if (type === 'info') colorClass = 'blue';
        M.toast({
            html: text,
            classes: colorClass + ' white-text',
            displayLength: 4000
        });
    }
}

/**
 * 달력 인라인 편집 기능
 */
function openInlineCalendarEdit($dayElement, date) {
    // 다른 편집 모드 종료
    $('.calendar-day.editing').each(function() {
        closeInlineCalendarEdit($(this), false);
    });

    $dayElement.addClass('editing');

    // 현재 데이터 가져오기
    const expense = monthlyExpensesData[date] || {
        total_amount: 0,
        food_cost: 0,
        necessities_cost: 0,
        transportation_cost: 0,
        other_cost: 0
    };

    // 편집 인터페이스 생성
    const editInterface = $(`
        <div class="inline-expense-edit">
            <input type="number" class="expense-edit-input" data-field="total_amount"
                   value="${expense.total_amount}" placeholder="총액" min="0">
            <div class="edit-actions">
                <button class="edit-btn save-btn">저장</button>
                <button class="edit-btn cancel-btn">취소</button>
            </div>
        </div>
    `);

    $dayElement.append(editInterface);

    // 입력 필드에 포커스
    editInterface.find('.expense-edit-input').focus().select();

    // 이벤트 핸들러
    editInterface.find('.save-btn').on('click', function(e) {
        e.stopPropagation();
        saveInlineCalendarEdit($dayElement, date);
    });

    editInterface.find('.cancel-btn').on('click', function(e) {
        e.stopPropagation();
        closeInlineCalendarEdit($dayElement, false);
    });

    // Enter 키로 저장
    editInterface.find('.expense-edit-input').on('keypress', function(e) {
        if (e.which === 13) { // Enter
            e.preventDefault();
            saveInlineCalendarEdit($dayElement, date);
        }
    });

    // ESC 키로 취소
    editInterface.find('.expense-edit-input').on('keydown', function(e) {
        if (e.which === 27) { // ESC
            e.preventDefault();
            closeInlineCalendarEdit($dayElement, false);
        }
    });

    // 외부 클릭시 취소
    $(document).on('click.calendarEdit', function(e) {
        if (!$(e.target).closest('.calendar-day.editing').length) {
            closeInlineCalendarEdit($dayElement, false);
        }
    });
}

function saveInlineCalendarEdit($dayElement, date) {
    const $input = $dayElement.find('.expense-edit-input');
    const newAmount = parseInt($input.val()) || 0;

    // 변경사항이 없으면 그냥 닫기
    const currentAmount = monthlyExpensesData[date] ? parseInt(monthlyExpensesData[date].total_amount) : 0;
    if (newAmount === currentAmount) {
        closeInlineCalendarEdit($dayElement, false);
        return;
    }

    // 버튼 비활성화
    $dayElement.find('.edit-btn').prop('disabled', true);

    // 로딩 시작
    const loadingId = 'save-inline-expense-' + date;
    if (typeof Feedback !== 'undefined') {
        Feedback.showLoading(loadingId, '저장 중...');
    }

    // API 데이터 준비
    const apiData = {
        expense_date: date,
        total_amount: newAmount,
        food_cost: 0,
        necessities_cost: 0,
        transportation_cost: 0,
        other_cost: 0
    };

    // 기존 데이터가 있으면 업데이트, 없으면 생성
    const expense = monthlyExpensesData[date];
    const apiUrl = expense ?
        `' + API_BASE_URL + '/daily-expenses/${expense.id}` :
        '' + API_BASE_URL + '/daily-expenses';
    const method = expense ? 'PUT' : 'POST';

    $.ajax({
        url: apiUrl,
        type: method,
        xhrFields: {
            withCredentials: true
        },
        contentType: 'application/json',
        data: JSON.stringify(apiData),
        success: function(response) {
            if (typeof Feedback !== 'undefined') {
                Feedback.hideLoading(loadingId);
            }

            if (response.success) {
                // 로컬 데이터 업데이트
                if (!monthlyExpensesData[date]) {
                    monthlyExpensesData[date] = {};
                }
                monthlyExpensesData[date].total_amount = newAmount;
                monthlyExpensesData[date].id = response.data ? response.data.id : expense.id;

                // UI 업데이트
                closeInlineCalendarEdit($dayElement, true);
                updateCalendarDay($dayElement, date, newAmount);
                updateMonthlyTotal();

                showMessage('지출이 저장되었습니다.', 'success');

                // 통계 업데이트
                updateExpenseStatistics();
            } else {
                showMessage(response.message || '지출 저장에 실패했습니다.', 'error');
                closeInlineCalendarEdit($dayElement, false);
            }

            // 버튼 활성화
            $dayElement.find('.edit-btn').prop('disabled', false);
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
            closeInlineCalendarEdit($dayElement, false);

            // 버튼 활성화
            $dayElement.find('.edit-btn').prop('disabled', false);
        }
    });
}

function closeInlineCalendarEdit($dayElement, saved) {
    $dayElement.removeClass('editing');
    $dayElement.find('.inline-expense-edit').remove();
    $(document).off('click.calendarEdit');
}

function updateCalendarDay($dayElement, date, amount) {
    // 기존 지출 정보 제거
    $dayElement.find('.calendar-expense-amount, .calendar-expense-detail').remove();

    // 새로운 지출 정보 추가
    if (amount > 0) {
        $dayElement.addClass('has-expense');

        let amountClass = 'low';
        if (amount >= 30000) amountClass = 'high';
        else if (amount >= 10000) amountClass = 'medium';

        const expenseContent = $(`
            <div class="calendar-expense-amount ${amountClass}">
                ₩${amount.toLocaleString()}
            </div>
            <div class="calendar-expense-detail">
                🍽️0 🛒0
            </div>
        `);

        $dayElement.append(expenseContent);
    } else {
        $dayElement.removeClass('has-expense');
    }
}