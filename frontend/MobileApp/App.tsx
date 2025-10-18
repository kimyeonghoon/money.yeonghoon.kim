import React from 'react';
import { View, ActivityIndicator, StyleSheet } from 'react-native';
import { AuthProvider, useAuth } from './src/contexts/AuthContext';
import { LoginScreen } from './src/screens/LoginScreen';
import { VerifyCodeScreen } from './src/screens/VerifyCodeScreen';
import { HomeScreen } from './src/screens/HomeScreen';

const AppContent: React.FC = () => {
  const { authStep, loading } = useAuth();

  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#007AFF" />
      </View>
    );
  }

  switch (authStep) {
    case 'login':
      return <LoginScreen />;
    case 'verify':
      return <VerifyCodeScreen />;
    case 'authenticated':
      return <HomeScreen />;
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
    backgroundColor: '#f5f5f5',
  },
});

export default App;
