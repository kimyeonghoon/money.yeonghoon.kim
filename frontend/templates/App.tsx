import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { Provider as PaperProvider } from 'react-native-paper';
import { AuthProvider } from './src/contexts/AuthContext';
import AppNavigator from './src/navigation/AppNavigator';

/**
 * 메인 App 컴포넌트
 *
 * 이 파일을 React Native 프로젝트의 App.tsx로 사용하세요.
 *
 * 필요한 작업:
 * 1. templates 폴더의 파일들을 src 폴더로 복사
 *    - api.ts -> src/services/api.ts
 *    - authService.ts -> src/services/authService.ts
 *    - AuthContext.tsx -> src/contexts/AuthContext.tsx
 *    - LoginScreen.tsx -> src/screens/LoginScreen.tsx
 *
 * 2. src/navigation/AppNavigator.tsx 생성하여 네비게이션 구성
 */

const App: React.FC = () => {
  return (
    <PaperProvider>
      <AuthProvider>
        <NavigationContainer>
          <AppNavigator />
        </NavigationContainer>
      </AuthProvider>
    </PaperProvider>
  );
};

export default App;
