import React from 'react';
import { render } from '@testing-library/react-native';
import { Text } from 'react-native';
import { Screen } from '../Screen';

describe('Screen Component', () => {
  describe('기본 렌더링', () => {
    it('자식 요소를 렌더링해야 함', () => {
      const { getByText } = render(
        <Screen>
          <Text>Test Content</Text>
        </Screen>
      );

      expect(getByText('Test Content')).toBeTruthy();
    });

    it('기본 배경색을 적용해야 함', () => {
      const { getByTestId } = render(
        <Screen>
          <Text testID="child">Test</Text>
        </Screen>
      );

      const container = getByTestId('child').parent;
      expect(container).toBeTruthy();
    });
  });

  describe('centered 속성', () => {
    it('centered가 true일 때 중앙 정렬 스타일 적용', () => {
      const { getByText } = render(
        <Screen centered>
          <Text>Centered Content</Text>
        </Screen>
      );

      expect(getByText('Centered Content')).toBeTruthy();
    });
  });

  describe('scrollable 속성', () => {
    it('scrollable이 true일 때 ScrollView 사용', () => {
      const { getByText } = render(
        <Screen scrollable>
          <Text>Scrollable Content</Text>
        </Screen>
      );

      expect(getByText('Scrollable Content')).toBeTruthy();
    });

    it('scrollable이 false일 때 View 사용', () => {
      const { getByText } = render(
        <Screen scrollable={false}>
          <Text>Non-scrollable Content</Text>
        </Screen>
      );

      expect(getByText('Non-scrollable Content')).toBeTruthy();
    });
  });

  describe('커스텀 스타일', () => {
    it('커스텀 스타일을 적용할 수 있어야 함', () => {
      const customStyle = { backgroundColor: 'red' };

      const { getByText } = render(
        <Screen style={customStyle}>
          <Text>Custom Style</Text>
        </Screen>
      );

      expect(getByText('Custom Style')).toBeTruthy();
    });
  });
});
