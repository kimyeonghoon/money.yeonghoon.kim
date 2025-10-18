/**
 * Sample React Native App
 * https://github.com/facebook/react-native
 *
 * @format
 */

import React from 'react';
import {
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';

function App(): React.JSX.Element {
  return (
    <View style={styles.container}>
      <ScrollView style={styles.scrollView} contentContainerStyle={styles.contentContainer}>
        <View style={styles.header}>
          <Text style={styles.headerText}>React Native 0.78.3</Text>
          <Text style={styles.subheaderText}>with React 19.0.0</Text>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>🎉 테스트 성공!</Text>
          <Text style={styles.sectionDescription}>
            React Native 0.78.3이 정상적으로 작동 중입니다.{'\n'}
            이 화면은 네이티브 앱과 웹 브라우저 모두에서 동작합니다!
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>✅ 빌드 정보</Text>
          <Text style={styles.sectionDescription}>
            • React Native: 0.78.3{'\n'}
            • React: 19.0.0{'\n'}
            • Android Gradle Plugin: 8.6.0{'\n'}
            • Gradle: 8.12
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>🚀 개발 환경</Text>
          <Text style={styles.sectionDescription}>
            • Metro 서버: 실행 중 (포트 8081){'\n'}
            • Webpack Dev Server: 실행 중 (포트 3000){'\n'}
            • WiFi 디버깅: 활성화됨{'\n'}
            • Hot Reload: 활성화됨
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>📱 플랫폼</Text>
          <Text style={styles.sectionDescription}>
            • Android: APK 빌드 지원{'\n'}
            • Web: React Native Web으로 브라우저 지원{'\n'}
            • 코드 한 번 작성으로 양쪽 플랫폼 실행!
          </Text>
        </View>
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  scrollView: {
    flex: 1,
  },
  contentContainer: {
    padding: 20,
  },
  header: {
    backgroundColor: '#007AFF',
    padding: 30,
    borderRadius: 15,
    marginBottom: 20,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.25,
    shadowRadius: 3.84,
    elevation: 5,
  },
  headerText: {
    fontSize: 32,
    fontWeight: '700',
    color: '#ffffff',
    marginBottom: 5,
  },
  subheaderText: {
    fontSize: 18,
    fontWeight: '500',
    color: '#ffffff',
    opacity: 0.9,
  },
  section: {
    backgroundColor: '#ffffff',
    padding: 20,
    borderRadius: 10,
    marginBottom: 15,
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 1,
    },
    shadowOpacity: 0.2,
    shadowRadius: 1.41,
    elevation: 2,
  },
  sectionTitle: {
    fontSize: 24,
    fontWeight: '600',
    color: '#333',
    marginBottom: 10,
  },
  sectionDescription: {
    fontSize: 16,
    fontWeight: '400',
    color: '#666',
    lineHeight: 24,
  },
});

export default App;
