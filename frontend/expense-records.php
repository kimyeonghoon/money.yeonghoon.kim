<?php
$pageTitle = '일간지출내역';
include 'includes/header.php';
?>

<link rel="stylesheet" href="css/expense-records.css">

<main class="container">
    <!-- 지출현황 -->
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <h5 class="section-title center-align" style="margin-bottom: 20px;">📊 지출현황</h5>
                        <div class="row">
                            <div class="col s12 m4">
                                <div class="center-align">
                                    <h6 style="color: #FF5722; margin: 0;">오늘 지출</h6>
                                    <span id="today-expenses-total" style="font-size: 20px; font-weight: bold; color: #FF5722;">₩0</span>
                                </div>
                            </div>
                            <div class="col s12 m4">
                                <div class="center-align">
                                    <h6 style="color: #2196F3; margin: 0;">이번 주</h6>
                                    <span id="week-expenses-total" style="font-size: 20px; font-weight: bold; color: #2196F3;">₩0</span>
                                </div>
                            </div>
                            <div class="col s12 m4">
                                <div class="center-align">
                                    <h6 style="color: #9C27B0; margin: 0;">이번 달</h6>
                                    <span id="month-expenses-total" style="font-size: 20px; font-weight: bold; color: #9C27B0;">₩0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 일간지출 기록 섹션 -->
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <div class="section-header">
                            <h5 class="section-title">📝 금일 지출 추가</h5>
                            <div class="section-header-actions">
                                <button id="add-expense-btn" class="btn-floating waves-effect waves-light green">
                                    <i class="material-icons">add</i>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- 월간 지출 달력 섹션 -->
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <div class="section-header">
                            <h5 class="section-title">📅 월간 지출 기록</h5>
                            <div class="section-header-actions">
                                <button id="prev-month-btn" class="btn-floating waves-effect waves-light blue">
                                    <i class="material-icons">chevron_left</i>
                                </button>
                                <span id="current-month-display" style="margin: 0 15px; font-weight: bold; color: #424242;">2025년 9월</span>
                                <button id="next-month-btn" class="btn-floating waves-effect waves-light blue">
                                    <i class="material-icons">chevron_right</i>
                                </button>
                            </div>
                        </div>

                        <!-- 월간 총계 요약 -->
                        <div class="row" style="margin-bottom: 20px;">
                            <div class="col s12">
                                <div class="card blue lighten-5" style="padding: 15px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                                        <h6 style="margin: 0; color: #1976D2;">📊 이번 달 총 지출</h6>
                                        <span id="monthly-total-amount" style="font-size: 20px; font-weight: bold; color: #1976D2;">₩0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 달력 -->
                        <div class="expense-calendar">
                            <div class="calendar-header">
                                <div class="calendar-day-header">일</div>
                                <div class="calendar-day-header">월</div>
                                <div class="calendar-day-header">화</div>
                                <div class="calendar-day-header">수</div>
                                <div class="calendar-day-header">목</div>
                                <div class="calendar-day-header">금</div>
                                <div class="calendar-day-header">토</div>
                            </div>
                            <div id="calendar-body" class="calendar-body">
                                <!-- 달력 내용이 여기에 동적으로 생성됩니다 -->
                            </div>
                        </div>

                        <!-- 범례 -->
                        <div class="row" style="margin-top: 20px;">
                            <div class="col s12">
                                <div class="center-align">
                                    <small style="color: #666;">
                                        <span style="display: inline-block; width: 12px; height: 12px; background-color: #f44336; border-radius: 50%; margin-right: 5px;"></span>
                                        높은 지출 (₩30,000+)
                                        <span style="margin: 0 15px;"></span>
                                        <span style="display: inline-block; width: 12px; height: 12px; background-color: #ff9800; border-radius: 50%; margin-right: 5px;"></span>
                                        보통 지출 (₩10,000~₩29,999)
                                        <span style="margin: 0 15px;"></span>
                                        <span style="display: inline-block; width: 12px; height: 12px; background-color: #4caf50; border-radius: 50%; margin-right: 5px;"></span>
                                        낮은 지출 (₩1~₩9,999)
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>


<!-- 일간지출 수정 모달 -->
<div id="edit-daily-expense-modal" class="modal modal-fixed-footer">
    <div class="modal-content">
        <h4><i class="material-icons left">edit</i>일간지출 편집</h4>
        <div class="row">
            <form id="edit-daily-expense-form" class="col s12">
                <input type="hidden" id="edit-daily-expense-id">
                <div class="row">
                    <div class="input-field col s12 m6">
                        <input id="edit-expense-date" type="date" class="validate" readonly style="background-color: #f5f5f5;">
                        <label for="edit-expense-date">지출일 (자동생성)</label>
                    </div>
                    <div class="input-field col s12 m6">
                        <input id="edit-total-amount" type="number" class="validate" readonly style="background-color: #f5f5f5;">
                        <label for="edit-total-amount">총 지출금액 (자동계산)</label>
                        <span class="helper-text">카테고리별 금액의 합계</span>
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s12 m3">
                        <input id="edit-food-cost" type="number" class="validate" min="0" max="999999999">
                        <label for="edit-food-cost">식비</label>
                        <span class="helper-text">원</span>
                    </div>
                    <div class="input-field col s12 m3">
                        <input id="edit-necessities-cost" type="number" class="validate" min="0" max="999999999">
                        <label for="edit-necessities-cost">생필품비</label>
                        <span class="helper-text">원</span>
                    </div>
                    <div class="input-field col s12 m3">
                        <input id="edit-transportation-cost" type="number" class="validate" min="0" max="999999999">
                        <label for="edit-transportation-cost">교통비</label>
                        <span class="helper-text">원</span>
                    </div>
                    <div class="input-field col s12 m3">
                        <input id="edit-other-cost" type="number" class="validate" min="0" max="999999999">
                        <label for="edit-other-cost">기타</label>
                        <span class="helper-text">원</span>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">취소</a>
        <a href="#!" class="waves-effect waves-light btn orange" id="save-daily-expense-edit">
            <i class="material-icons left">save</i>저장
        </a>
    </div>
</div>

<!-- 지출 추가 모달 -->
<div id="add-expense-modal" class="modal modal-fixed-footer">
    <div class="modal-content">
        <h4><i class="material-icons left">add</i>오늘 지출 추가</h4>
        <p style="color: #666; margin-bottom: 20px;">오늘 사용한 금액을 카테고리별로 입력하세요. 기존 금액에 추가됩니다.</p>
        <div class="row">
            <form id="add-expense-form" class="col s12">
                <div class="row">
                    <div class="input-field col s12 m6">
                        <input id="add-food-cost" type="number" class="validate" min="0" max="999999999" value="0">
                        <label for="add-food-cost">🍽️ 식비</label>
                        <span class="helper-text">원</span>
                    </div>
                    <div class="input-field col s12 m6">
                        <input id="add-necessities-cost" type="number" class="validate" min="0" max="999999999" value="0">
                        <label for="add-necessities-cost">🛒 생활비</label>
                        <span class="helper-text">원</span>
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s12 m6">
                        <input id="add-transportation-cost" type="number" class="validate" min="0" max="999999999" value="0">
                        <label for="add-transportation-cost">🚌 교통비</label>
                        <span class="helper-text">원</span>
                    </div>
                    <div class="input-field col s12 m6">
                        <input id="add-other-cost" type="number" class="validate" min="0" max="999999999" value="0">
                        <label for="add-other-cost">💰 기타</label>
                        <span class="helper-text">원</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col s12">
                        <div class="card blue lighten-5" style="padding: 15px;">
                            <h6 style="margin: 0 0 10px 0; color: #1976D2;">📋 추가할 총액</h6>
                            <span id="add-total-preview" style="font-size: 18px; font-weight: bold; color: #1976D2;">₩0</span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">취소</a>
        <a href="#!" class="waves-effect waves-light btn orange" id="save-add-expense">
            <i class="material-icons left">add</i>지출 추가
        </a>
    </div>
</div>

<script src="js/expense-records.js?v=<?php echo time(); ?>"></script>

<?php include 'includes/footer.php'; ?>