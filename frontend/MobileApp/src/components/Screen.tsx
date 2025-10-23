import React from 'react';
import { View, StyleSheet, ViewStyle, ScrollView } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { colors, spacing } from '../theme';

interface ScreenProps {
  children: React.ReactNode;
  style?: ViewStyle;
  scrollable?: boolean;
  centered?: boolean;
}

export const Screen: React.FC<ScreenProps> = ({
  children,
  style,
  scrollable = false,
  centered = false,
}) => {
  const containerStyle = [
    styles.container,
    centered && styles.centered,
    style,
  ];

  if (scrollable) {
    return (
      <SafeAreaView style={styles.safeArea}>
        <ScrollView
          style={styles.scrollContainer}
          contentContainerStyle={[
            styles.scrollContent,
            centered && styles.centered,
            style,
          ]}
        >
          {children}
        </ScrollView>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={containerStyle}>{children}</View>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: colors.background,
  },
  container: {
    flex: 1,
    backgroundColor: colors.background,
    padding: spacing.lg,
    paddingBottom: 0,
  },
  scrollContainer: {
    flex: 1,
    backgroundColor: colors.background,
  },
  scrollContent: {
    flexGrow: 1,
    padding: spacing.lg,
    paddingBottom: spacing.xl,
  },
  centered: {
    justifyContent: 'center',
    alignItems: 'center',
  },
});
