<?php
$pageTitle = '선납비용';
include 'includes/header.php';
?>

    <main class="container">
        <div class="section fade-in">
            <div class="row">
                <div class="col s12">
                    <h4 class="section-title"><i class="material-icons left">payment</i>선납비용 관리</h4>
                </div>
            </div>

            <!-- 추가/수정 폼 -->
            <div id="expense-form" class="card" style="display: none;">
                <div class="card-content">
                    <span class="card-title" id="form-title">선납비용 추가</span>
                    <form>
                        <div class="row">
                            <div class="input-field col s12 m6">
                                <select id="category">
                                    <option value="" disabled selected>선택하세요</option>
                                    <option value="보험료">🛡️ 보험료</option>
                                    <option value="구독">📺 구독</option>
                                    <option value="교육비">📚 교육비</option>
                                    <option value="회원권">🏋️ 회원권</option>
                                    <option value="연회비">💳 연회비</option>
                                    <option value="세금">🏢 세금</option>
                                    <option value="기타">📝 기타</option>
                                </select>
                                <label>카테고리*</label>
                            </div>
                            <div class="input-field col s12 m6">
                                <input id="item-name" type="text" class="validate" required>
                                <label for="item-name">항목명*</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12 m4">
                                <input id="amount" type="number" class="validate" required>
                                <label for="amount">선납금액* (원)</label>
                            </div>
                            <div class="input-field col s12 m4">
                                <input id="prepaid-date" type="date" class="validate" required>
                                <label for="prepaid-date">선납일자*</label>
                            </div>
                            <div class="input-field col s12 m4">
                                <input id="expiry-date" type="date" class="validate" required>
                                <label for="expiry-date">만료일자*</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12 m4">
                                <input id="monthly-amount" type="number" readonly>
                                <label for="monthly-amount">월할금액 (자동 계산)</label>
                            </div>
                            <div class="input-field col s12 m4">
                                <select id="payment-method">
                                    <option value="체크">💳 체크카드</option>
                                    <option value="신용">🏦 신용카드</option>
                                    <option value="계좌이체">📱 계좌이체</option>
                                    <option value="현금">💵 현금</option>
                                </select>
                                <label>결제수단</label>
                            </div>
                            <div class="input-field col s12 m4">
                                <select id="status">
                                    <option value="active">✅ 활성</option>
                                    <option value="expired">⏰ 만료</option>
                                    <option value="cancelled">❌ 취소</option>
                                </select>
                                <label>상태</label>
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
                    <button id="add-expense-btn" class="btn waves-effect waves-light">
                        <i class="material-icons left">add</i>새 선납비용 추가
                    </button>
                    <button id="refresh-btn" class="btn grey waves-effect waves-light">
                        <i class="material-icons left">refresh</i>새로고침
                    </button>
                </div>
            </div>

            <!-- 선납비용 목록 테이블 -->
            <div class="row">
                <div class="col s12">
                    <div id="loading" class="center-align">
                        <div class="preloader-wrapper active">
                            <div class="spinner-layer spinner-teal-only">
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
                        <p>선납비용 목록을 불러오는 중...</p>
                    </div>

                    <div class="card" id="expenses-table-card" style="display: none;">
                        <div class="card-content">
                            <div class="responsive-table">
                                <table class="striped">
                                    <thead>
                                        <tr>
                                            <th>상태</th>
                                            <th>카테고리</th>
                                            <th>항목명</th>
                                            <th>선납금액</th>
                                            <th>월할금액</th>
                                            <th>선납일자</th>
                                            <th>만료일자</th>
                                            <th>남은기간</th>
                                            <th>결제수단</th>
                                            <th>비고</th>
                                            <th>관리</th>
                                        </tr>
                                    </thead>
                                    <tbody id="expenses-table-body">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div id="no-data" class="card grey lighten-4" style="display: none;">
                        <div class="card-content center-align">
                            <span class="card-title grey-text">
                                <i class="material-icons large">payment</i>
                            </span>
                            <p class="grey-text">등록된 선납비용이 없습니다.</p>
                            <p class="grey-text">새 비용을 추가해보세요! 💸</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 선납비용 통계 -->
            <div class="row">
                <div class="col s12 m6 l3">
                    <div class="stats-card teal white-text">
                        <div class="stats-icon">
                            <i class="material-icons">attach_money</i>
                        </div>
                        <div class="stats-number" id="total-prepaid">-</div>
                        <div class="stats-label">총 선납금액</div>
                    </div>
                </div>
                <div class="col s12 m6 l3">
                    <div class="stats-card blue white-text">
                        <div class="stats-icon">
                            <i class="material-icons">calendar_month</i>
                        </div>
                        <div class="stats-number" id="monthly-equivalent">-</div>
                        <div class="stats-label">월할환산 금액</div>
                    </div>
                </div>
                <div class="col s12 m6 l3">
                    <div class="stats-card green white-text">
                        <div class="stats-icon">
                            <i class="material-icons">check_circle</i>
                        </div>
                        <div class="stats-number" id="active-count">-</div>
                        <div class="stats-label">활성 항목</div>
                    </div>
                </div>
                <div class="col s12 m6 l3">
                    <div class="stats-card orange white-text">
                        <div class="stats-icon">
                            <i class="material-icons">warning</i>
                        </div>
                        <div class="stats-number" id="expiring-soon">-</div>
                        <div class="stats-label">곧 만료예정</div>
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
let editingExpenseId = null;

$(document).ready(function() {
    loadExpenses();

    // 날짜 변경 시 월할금액 자동 계산
    $('#prepaid-date, #expiry-date, #amount').on('change', calculateMonthlyAmount);

    // 이벤트 핸들러
    $('#add-expense-btn').click(showAddForm);
    $('#cancel-btn').click(hideForm);
    $('#save-btn').click(saveExpense);
    $('#refresh-btn').click(loadExpenses);
});

function calculateMonthlyAmount() {
    let prepaidDate = $('#prepaid-date').val();
    let expiryDate = $('#expiry-date').val();
    let amount = parseInt($('#amount').val()) || 0;

    if (prepaidDate && expiryDate && amount > 0) {
        let startDate = new Date(prepaidDate);
        let endDate = new Date(expiryDate);
        let diffTime = Math.abs(endDate - startDate);
        let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        let months = diffDays / 30.44; // 평균 월일수

        if (months > 0) {
            let monthlyAmount = Math.round(amount / months);
            $('#monthly-amount').val(monthlyAmount);
        }
    }
}

function loadExpenses() {
    $('#loading').show();
    $('#expenses-table-card').hide();
    $('#no-data').hide();

    $.ajax({
        url: '/api/prepaid-expenses',
        method: 'GET',
        success: function(response) {
            $('#loading').hide();
            if (response.success) {
                if (response.data.length === 0) {
                    $('#no-data').show();
                    updateSummary([]);
                } else {
                    displayExpenses(response.data);
                    updateSummary(response.data);
                    $('#expenses-table-card').show();
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

function displayExpenses(expenses) {
    let tbody = $('#expenses-table-body');
    tbody.empty();

    expenses.forEach(function(expense) {
        let statusIcon, statusClass;

        // API 응답의 is_active 필드 기반으로 상태 결정
        let isActive = expense.is_active == 1;
        let remainingDays = calculateRemainingDays(expense.expiry_date);

        if (isActive && remainingDays > 0) {
            statusIcon = '✅';
            statusClass = 'positive';
        } else if (isActive && remainingDays <= 0) {
            statusIcon = '⏰';
            statusClass = 'negative';
        } else {
            statusIcon = '❌';
            statusClass = 'negative';
        }

        let remainingText = remainingDays > 0 ? remainingDays + '일' : '만료됨';

        // 월할금액 계산 (만약 없다면 자동 계산)
        let monthlyAmount = expense.monthly_amount;
        if (!monthlyAmount && expense.prepaid_date && expense.expiry_date) {
            let startDate = new Date(expense.prepaid_date);
            let endDate = new Date(expense.expiry_date);
            let diffTime = Math.abs(endDate - startDate);
            let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            let months = diffDays / 30.44;
            monthlyAmount = months > 0 ? Math.round(expense.amount / months) : 0;
        }

        let row = '<tr>' +
                  '<td class="' + statusClass + '" style="font-weight: bold;">' + statusIcon + '</td>' +
                  '<td>' + (expense.category || '-') + '</td>' +
                  '<td>' + (expense.item_name || '-') + '</td>' +
                  '<td style="font-weight: bold; color: #0066cc;">' + formatMoney(expense.amount) + '</td>' +
                  '<td style="font-weight: bold; color: #0066cc;">' + formatMoney(monthlyAmount || 0) + '</td>' +
                  '<td>' + formatDate(expense.prepaid_date) + '</td>' +
                  '<td>' + formatDate(expense.expiry_date) + '</td>' +
                  '<td' + (remainingDays < 30 && remainingDays > 0 ? ' style="color: #ff6600; font-weight: bold;"' : '') + '>' + remainingText + '</td>' +
                  '<td>' + (expense.payment_method || '-') + '</td>' +
                  '<td>' + (expense.notes || '-') + '</td>' +
                  '<td>' +
                  '<button onclick="editExpense(' + expense.id + ')" class="btn-small waves-effect waves-light blue" style="margin-right: 5px;"><i class="material-icons left">edit</i>수정</button>' +
                  '<button onclick="deleteExpense(' + expense.id + ')" class="btn-small waves-effect waves-light red"><i class="material-icons left">delete</i>삭제</button>' +
                  '</td>' +
                  '</tr>';
        tbody.append(row);
    });

}

function calculateRemainingDays(expiryDate) {
    let today = new Date();
    let expiry = new Date(expiryDate);
    let diffTime = expiry - today;
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
}

function updateSummary(expenses) {
    let totalPrepaid = 0;
    let monthlyEquivalent = 0;
    let activeCount = 0;
    let expiringSoon = 0;

    expenses.forEach(function(expense) {
        totalPrepaid += parseInt(expense.amount) || 0;

        if (expense.is_active == 1) {
            activeCount++;

            // 월할금액 계산
            let monthlyAmount = expense.monthly_amount;
            if (!monthlyAmount && expense.prepaid_date && expense.expiry_date) {
                let startDate = new Date(expense.prepaid_date);
                let endDate = new Date(expense.expiry_date);
                let diffTime = Math.abs(endDate - startDate);
                let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                let months = diffDays / 30.44;
                monthlyAmount = months > 0 ? Math.round(expense.amount / months) : 0;
            }

            monthlyEquivalent += parseInt(monthlyAmount) || 0;

            let remainingDays = calculateRemainingDays(expense.expiry_date);
            if (remainingDays > 0 && remainingDays <= 30) {
                expiringSoon++;
            }
        }
    });

    $('#total-prepaid').text(formatMoney(totalPrepaid));
    $('#monthly-equivalent').text(formatMoney(monthlyEquivalent));
    $('#active-count').text(activeCount + '개');
    $('#expiring-soon').text(expiringSoon + '개');
}

function showAddForm() {
    editingExpenseId = null;
    $('#form-title').text('선납비용 추가');
    clearForm();
    $('#expense-form').show();
    $('#category').focus();
}

function editExpense(id) {
    editingExpenseId = id;
    $('#form-title').text('선납비용 수정');

    $.ajax({
        url: '/api/prepaid-expenses/' + id,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                fillForm(response.data);
                $('#expense-form').show();
            } else {
                showMessage('비용 정보 로드 실패: ' + response.message, 'error');
            }
        },
        error: function() {
            showMessage('비용 정보를 불러올 수 없습니다.', 'error');
        }
    });
}

function fillForm(expense) {
    $('#category').val(expense.category || '보험료');
    $('#item-name').val(expense.item_name || '');
    $('#amount').val(expense.amount || 0);
    $('#prepaid-date').val(expense.prepaid_date || '');
    $('#expiry-date').val(expense.expiry_date || '');
    $('#monthly-amount').val(expense.monthly_amount || 0);
    $('#payment-method').val(expense.payment_method || '체크');

    // is_active를 status로 변환
    let status = 'active';
    if (expense.is_active == 1) {
        let remainingDays = calculateRemainingDays(expense.expiry_date);
        status = remainingDays > 0 ? 'active' : 'expired';
    } else {
        status = 'cancelled';
    }
    $('#status').val(status);

    $('#notes').val(expense.notes || '');
}

function clearForm() {
    $('#category').val('보험료');
    $('#item-name').val('');
    $('#amount').val('');
    $('#prepaid-date').val('');
    $('#expiry-date').val('');
    $('#monthly-amount').val('');
    $('#payment-method').val('체크');
    $('#status').val('active');
    $('#notes').val('');
}

function hideForm() {
    $('#expense-form').hide();
    editingExpenseId = null;
}

function saveExpense() {
    let data = {
        category: $('#category').val(),
        item_name: $('#item-name').val().trim(),
        amount: parseInt($('#amount').val()) || 0,
        prepaid_date: $('#prepaid-date').val(),
        expiry_date: $('#expiry-date').val(),
        monthly_amount: parseInt($('#monthly-amount').val()) || 0,
        payment_method: $('#payment-method').val(),
        is_active: $('#status').val() === 'cancelled' ? 0 : 1, // status를 is_active로 변환
        notes: $('#notes').val().trim()
    };

    // 유효성 검사
    if (!data.item_name || data.amount <= 0 || !data.prepaid_date || !data.expiry_date) {
        showMessage('필수 항목을 모두 입력해주세요.', 'error');
        return;
    }

    // 날짜 유효성 검사
    if (new Date(data.prepaid_date) >= new Date(data.expiry_date)) {
        showMessage('만료일자는 선납일자보다 이후여야 합니다.', 'error');
        return;
    }

    let url = '/api/prepaid-expenses';
    let method = 'POST';

    if (editingExpenseId) {
        url += '/' + editingExpenseId;
        method = 'PUT';
    }

    $.ajax({
        url: url,
        method: method,
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            if (response.success) {
                showMessage(editingExpenseId ? '선납비용이 수정되었습니다.' : '선납비용이 추가되었습니다.', 'success');
                hideForm();
                loadExpenses();
            } else {
                showMessage('저장 실패: ' + response.message, 'error');
            }
        },
        error: function() {
            showMessage('서버 오류가 발생했습니다.', 'error');
        }
    });
}

function deleteExpense(id) {
    if (!confirm('이 선납비용을 삭제하시겠습니까?')) {
        return;
    }

    $.ajax({
        url: '/api/prepaid-expenses/' + id,
        method: 'DELETE',
        success: function(response) {
            if (response.success) {
                showMessage('선납비용이 삭제되었습니다.', 'success');
                loadExpenses();
            } else {
                showMessage('삭제 실패: ' + response.message, 'error');
            }
        },
        error: function() {
            showMessage('서버 오류가 발생했습니다.', 'error');
        }
    });
}

function formatDate(dateString) {
    if (!dateString) return '-';
    let date = new Date(dateString);
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