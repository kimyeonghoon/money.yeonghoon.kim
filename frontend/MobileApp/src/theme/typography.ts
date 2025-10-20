import { TextStyle } from 'react-native';

export const typography = {
  title: {
    fontSize: 28,
    fontWeight: 'bold' as TextStyle['fontWeight'],
    color: '#333',
  },
  subtitle: {
    fontSize: 14,
    fontWeight: 'normal' as TextStyle['fontWeight'],
    color: '#666',
  },
  body: {
    fontSize: 16,
    fontWeight: 'normal' as TextStyle['fontWeight'],
    color: '#333',
  },
  bodySmall: {
    fontSize: 14,
    fontWeight: 'normal' as TextStyle['fontWeight'],
    color: '#333',
  },
  caption: {
    fontSize: 12,
    fontWeight: 'normal' as TextStyle['fontWeight'],
    color: '#666',
  },
  button: {
    fontSize: 16,
    fontWeight: '600' as TextStyle['fontWeight'],
    color: '#fff',
  },
  label: {
    fontSize: 12,
    fontWeight: '600' as TextStyle['fontWeight'],
    color: '#666',
  },
} as const;

export type TypographyKey = keyof typeof typography;
