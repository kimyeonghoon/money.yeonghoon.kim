import React from 'react';
import { View, StyleSheet, ViewStyle, ScrollView } from 'react-native';
import { colors, spacing } from '../theme';

interface ScreenProps {
  children: React.ReactNode;
  style?: ViewStyle;
  scrollable?: boolean;
  centered?: boolean;
  noPadding?: boolean;
}

export const Screen: React.FC<ScreenProps> = ({
  children,
  style,
  scrollable = false,
  centered = false,
  noPadding = false,
}) => {
  const containerStyle = [
    styles.container,
    noPadding && styles.noPadding,
    centered && styles.centered,
    style,
  ];

  if (scrollable) {
    return (
      <ScrollView
        style={[styles.container, noPadding && styles.noPadding]}
        contentContainerStyle={[
          styles.scrollContent,
          noPadding && styles.noPadding,
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
  noPadding: {
    padding: 0,
  },
  centered: {
    justifyContent: 'center',
    alignItems: 'center',
  },
});
