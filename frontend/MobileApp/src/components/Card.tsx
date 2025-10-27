import React from 'react';
import { View, StyleSheet, ViewStyle } from 'react-native';
import { colors, borderRadius, shadows, spacing } from '../theme';

interface CardProps {
  children: React.ReactNode;
  style?: ViewStyle;
  noPadding?: boolean;
  noShadow?: boolean;
  noMaxWidth?: boolean;
}

export const Card: React.FC<CardProps> = ({
  children,
  style,
  noPadding = false,
  noShadow = false,
  noMaxWidth = false,
}) => {
  return (
    <View
      style={[
        styles.card,
        noMaxWidth && styles.noMaxWidth,
        !noPadding && styles.withPadding,
        !noShadow && shadows.card,
        style,
      ]}
    >
      {children}
    </View>
  );
};

const styles = StyleSheet.create({
  card: {
    width: '100%',
    maxWidth: 400,
    backgroundColor: colors.cardBackground,
    borderRadius: borderRadius.medium,
  },
  noMaxWidth: {
    maxWidth: '100%',
  },
  withPadding: {
    padding: spacing.xl,
  },
});
