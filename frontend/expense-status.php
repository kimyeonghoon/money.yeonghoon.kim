<?php
$pageTitle = '지출현황';
include 'includes/header.php';
?>

<link rel="stylesheet" href="css/expense-status.css">

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
                        <h5 class="section-title center-align" style="margin-bottom: 20px;">💰 월간 고정지출현황</h5>
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
                                    <h6 style="color: #FF5722; margin: 0;">총 고정지출</h6>
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

<script src="js/expense-status.js"></script>

<?php include 'includes/footer.php'; ?>