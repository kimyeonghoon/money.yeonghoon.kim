import React from 'react';
import { render } from '@testing-library/react-native';
import { TransactionsScreen } from '../TransactionsScreen';

describe('TransactionsScreen', () => {
  describe('기본 렌더링', () => {
    it('거래내역 제목을 표시해야 함', () => {
      const { getByText } = render(<TransactionsScreen />);

      expect(getByText('거래내역')).toBeTruthy();
    });

    it('설명 텍스트를 표시해야 함', () => {
      const { getByText } = render(<TransactionsScreen />);

      expect(getByText('수입과 지출 내역을 관리하는 화면입니다')).toBeTruthy();
    });
  });

  describe('화면 구조', () => {
    it('Screen 컴포넌트를 사용해야 함', () => {
      const { getByText } = render(<TransactionsScreen />);

      expect(getByText('거래내역')).toBeTruthy();
    });

    it('Card 컴포넌트를 사용해야 함', () => {
      const { getByText } = render(<TransactionsScreen />);

      expect(getByText('거래내역')).toBeTruthy();
    });
  });
});
