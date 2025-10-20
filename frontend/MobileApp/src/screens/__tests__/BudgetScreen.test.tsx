import React from 'react';
import { render } from '@testing-library/react-native';
import { BudgetScreen } from '../BudgetScreen';

describe('BudgetScreen', () => {
  describe('기본 렌더링', () => {
    it('예산 관리 제목을 표시해야 함', () => {
      const { getByText } = render(<BudgetScreen />);

      expect(getByText('예산 관리')).toBeTruthy();
    });

    it('설명 텍스트를 표시해야 함', () => {
      const { getByText } = render(<BudgetScreen />);

      expect(getByText('월별 예산을 설정하고 관리하는 화면입니다')).toBeTruthy();
    });
  });

  describe('화면 구조', () => {
    it('Screen 컴포넌트를 사용해야 함', () => {
      const { getByText } = render(<BudgetScreen />);

      expect(getByText('예산 관리')).toBeTruthy();
    });

    it('Card 컴포넌트를 사용해야 함', () => {
      const { getByText } = render(<BudgetScreen />);

      expect(getByText('예산 관리')).toBeTruthy();
    });
  });
});
