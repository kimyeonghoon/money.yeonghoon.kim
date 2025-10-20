export const borderRadius = {
  small: 8,
  medium: 12,
  large: 16,
  full: 9999,
} as const;

export type BorderRadiusKey = keyof typeof borderRadius;
