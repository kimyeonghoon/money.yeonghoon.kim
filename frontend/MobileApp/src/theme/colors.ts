export const colors = {
  primary: '#007AFF',
  danger: '#FF3B30',
  success: '#34C759',
  warning: '#FF9500',

  background: '#f5f5f5',
  cardBackground: '#fff',

  text: '#333',
  textSecondary: '#666',
  textLight: '#999',

  border: '#ddd',
  borderFocus: '#007AFF',

  disabled: '#999',
  overlay: 'rgba(0, 0, 0, 0.5)',
} as const;

export type ColorKey = keyof typeof colors;
