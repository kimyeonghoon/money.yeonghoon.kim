import React from 'react';
import { View, StyleSheet, ViewStyle, ScrollView } from 'react-native';
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
      <ScrollView
        style={styles.container}
        contentContainerStyle={[
          styles.scrollContent,
          centered && styles.centered,
          style,
        ]}
      >
        {children}
      </ScrollView>
    );
  }

  return <View style={containerStyle}>{children}</View>;
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.background,
    padding: spacing.lg,
  },
  scrollContent: {
    flexGrow: 1,
    padding: spacing.lg,
  },
  centered: {
    justifyContent: 'center',
    alignItems: 'center',
  },
});
