import React from 'react';
import { View, ActivityIndicator, StyleSheet } from 'react-native';
import { NavigationContainer } from '@react-navigation/native';
import { AuthProvider, useAuth } from './src/contexts/AuthContext';
import { LoginScreen } from './src/screens/LoginScreen';
import { VerifyCodeScreen } from './src/screens/VerifyCodeScreen';
import { MainTabNavigator } from './src/navigation/MainTabNavigator';
import { colors } from './src/theme';

const AppContent: React.FC = () => {
  const { authStep, loading } = useAuth();

  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color={colors.primary} />
      </View>
    );
  }

  switch (authStep) {
    case 'login':
      return <LoginScreen />;
    case 'verify':
      return <VerifyCodeScreen />;
    case 'authenticated':
      return (
        <NavigationContainer>
          <MainTabNavigator />
        </NavigationContainer>
      );
    default:
      return <LoginScreen />;
  }
};

const App: React.FC = () => {
  return (
    <AuthProvider>
      <AppContent />
    </AuthProvider>
  );
};

const styles = StyleSheet.create({
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: colors.background,
  },
});

export default App;
