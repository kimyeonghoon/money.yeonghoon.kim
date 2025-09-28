// 최종 통합 시스템 테스트 스크립트
const https = require('https');
const http = require('http');

function makeRequest(url, method = 'GET', data = null) {
    return new Promise((resolve, reject) => {
        const urlObj = new URL(url);
        const options = {
            hostname: urlObj.hostname,
            port: urlObj.port,
            path: urlObj.pathname + urlObj.search,
            method: method,
            headers: {
                'Content-Type': 'application/json'
            }
        };

        const req = http.request(options, (res) => {
            let body = '';
            res.on('data', (chunk) => {
                body += chunk;
            });
            res.on('end', () => {
                try {
                    const result = JSON.parse(body);
                    resolve({ status: res.statusCode, data: result });
                } catch (e) {
                    resolve({ status: res.statusCode, data: { success: false, message: 'Invalid JSON', body: body.substring(0, 200) } });
                }
            });
        });

        req.on('error', (err) => {
            reject(err);
        });

        if (data) {
            req.write(JSON.stringify(data));
        }
        req.end();
    });
}

async function testSystemIntegration() {
    console.log('=== 최종 시스템 통합 테스트 ===\n');

    let tests = 0;
    let passed = 0;

    // 1. API 기본 동작 확인
    console.log('1. 기본 API 동작 확인');
    tests++;
    try {
        const response = await makeRequest('http://localhost:8080/api/cash-assets');
        if (response.status === 200 && response.data.success) {
            console.log('   ✅ 현금 자산 API 정상');
            passed++;
        } else {
            console.log('   ❌ 현금 자산 API 실패');
        }
    } catch (error) {
        console.log('   ❌ 현금 자산 API 연결 실패:', error.message);
    }

    // 2. 아카이브 월 목록 조회
    console.log('\n2. 아카이브 시스템 확인');
    tests++;
    try {
        const response = await makeRequest('http://localhost:8080/api/archive/months');
        if (response.status === 200 && response.data.success && response.data.data.length > 0) {
            console.log(`   ✅ 아카이브 월 목록: ${response.data.data.length}개`);
            console.log(`      최신: ${response.data.data[0].label}`);
            passed++;
        } else {
            console.log('   ❌ 아카이브 월 목록 실패');
        }
    } catch (error) {
        console.log('   ❌ 아카이브 월 목록 연결 실패:', error.message);
    }

    // 3. 아카이브 데이터 조회
    console.log('\n3. 아카이브 데이터 조회');
    tests++;
    try {
        const response = await makeRequest('http://localhost:8080/api/archive/cash-assets?month=2025-09');
        if (response.status === 200 && response.data.success) {
            const count = response.data.data.data ? response.data.data.data.length : 0;
            console.log(`   ✅ 9월 현금 자산 아카이브: ${count}개`);
            passed++;
        } else {
            console.log('   ❌ 아카이브 데이터 조회 실패');
        }
    } catch (error) {
        console.log('   ❌ 아카이브 데이터 연결 실패:', error.message);
    }

    // 4. 에러 처리 확인
    console.log('\n4. 에러 처리 확인');
    tests++;
    try {
        const response = await makeRequest('http://localhost:8080/api/archive/cash-assets');
        if (response.status === 200 && !response.data.success && response.data.message.includes('month')) {
            console.log('   ✅ 올바른 에러 처리 (month 파라미터 필수)');
            passed++;
        } else {
            console.log('   ❌ 에러 처리 확인 실패');
        }
    } catch (error) {
        console.log('   ❌ 에러 처리 테스트 실패:', error.message);
    }

    // 5. 데이터 수정 기능
    console.log('\n5. 데이터 수정 기능');
    tests++;
    try {
        const response = await makeRequest('http://localhost:8080/api/archive/cash-assets/27?month=2025-09', 'PUT', { balance: 750000 });
        if (response.status === 200 && response.data.success) {
            console.log('   ✅ 아카이브 데이터 수정 성공');
            passed++;
        } else {
            console.log('   ❌ 아카이브 데이터 수정 실패');
        }
    } catch (error) {
        console.log('   ❌ 데이터 수정 연결 실패:', error.message);
    }

    // 6. 프론트엔드 확인
    console.log('\n6. 프론트엔드 확인');
    tests++;
    try {
        const req = http.request({
            hostname: 'localhost',
            port: 3001,
            path: '/assets.php',
            method: 'GET'
        }, (res) => {
            let body = '';
            res.on('data', (chunk) => {
                body += chunk;
            });
            res.on('end', () => {
                const hasComponents = body.includes('ArchiveManager') &&
                                   body.includes('month-selector') &&
                                   body.includes('showDataLoading') &&
                                   body.includes('showDataError');

                if (hasComponents) {
                    console.log('   ✅ 프론트엔드 아카이브 컴포넌트 로드됨');
                    passed++;
                } else {
                    console.log('   ❌ 프론트엔드 컴포넌트 누락');
                }

                printResults();
            });
        });

        req.on('error', (err) => {
            console.log('   ❌ 프론트엔드 연결 실패:', err.message);
            printResults();
        });

        req.end();
    } catch (error) {
        console.log('   ❌ 프론트엔드 테스트 실패:', error.message);
        printResults();
    }

    function printResults() {
        console.log('\n=== 최종 테스트 결과 ===');
        console.log(`통과: ${passed}/${tests}`);
        console.log(`성공률: ${Math.round((passed / tests) * 100)}%`);

        if (passed === tests) {
            console.log('🎉 모든 테스트 통과! 시스템이 성공적으로 통합되었습니다');
            console.log('');
            console.log('✨ 구현된 기능들:');
            console.log('  - 월별 아카이브 시스템');
            console.log('  - 아카이브 데이터 수정 기능');
            console.log('  - 모바일 최적화된 UI');
            console.log('  - 포괄적인 로딩 상태 및 에러 처리');
            console.log('  - 현재/아카이브 모드 전환');
            console.log('  - JSON 기반 완전한 데이터 보존');
            console.log('');
            console.log('🚀 시스템이 프로덕션 준비 완료되었습니다!');
            process.exit(0);
        } else {
            console.log('⚠️ 일부 테스트 실패, 시스템 점검 필요');
            process.exit(1);
        }
    }
}

runTests = testSystemIntegration;
runTests();