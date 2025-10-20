import React from 'react';
import { render } from '@testing-library/react-native';
import { Text } from 'react-native';
import { Card } from '../Card';

describe('Card Component', () => {
  describe('기본 렌더링', () => {
    it('자식 요소를 렌더링해야 함', () => {
      const { getByText } = render(
        <Card>
          <Text>Card Content</Text>
        </Card>
      );

      expect(getByText('Card Content')).toBeTruthy();
    });

    it('기본적으로 패딩이 적용되어야 함', () => {
      const { getByText } = render(
        <Card>
          <Text>Padded Content</Text>
        </Card>
      );

      expect(getByText('Padded Content')).toBeTruthy();
    });
  });

  describe('noPadding 속성', () => {
    it('noPadding이 true일 때 패딩이 없어야 함', () => {
      const { getByText } = render(
        <Card noPadding>
          <Text>No Padding Content</Text>
        </Card>
      );

      expect(getByText('No Padding Content')).toBeTruthy();
    });
  });

  describe('noShadow 속성', () => {
    it('noShadow가 true일 때 그림자가 없어야 함', () => {
      const { getByText } = render(
        <Card noShadow>
          <Text>No Shadow Content</Text>
        </Card>
      );

      expect(getByText('No Shadow Content')).toBeTruthy();
    });
  });

  describe('커스텀 스타일', () => {
    it('커스텀 스타일을 적용할 수 있어야 함', () => {
      const customStyle = { marginBottom: 20 };

      const { getByText } = render(
        <Card style={customStyle}>
          <Text>Custom Style Card</Text>
        </Card>
      );

      expect(getByText('Custom Style Card')).toBeTruthy();
    });
  });

  describe('복합 속성', () => {
    it('noPadding과 noShadow를 동시에 적용할 수 있어야 함', () => {
      const { getByText } = render(
        <Card noPadding noShadow>
          <Text>Clean Card</Text>
        </Card>
      );

      expect(getByText('Clean Card')).toBeTruthy();
    });
  });
});
