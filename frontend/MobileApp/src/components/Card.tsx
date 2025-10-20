import React from 'react';
import { View, StyleSheet, ViewStyle } from 'react-native';
import { colors, borderRadius, shadows, spacing } from '../theme';

interface CardProps {
  children: React.ReactNode;
  style?: ViewStyle;
  noPadding?: boolean;
  noShadow?: boolean;
}

export const Card: React.FC<CardProps> = ({
  children,
  style,
  noPadding = false,
  noShadow = false,
}) => {
  return (
    <View
      style={[
        styles.card,
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
  withPadding: {
    padding: spacing.xl,
  },
});
