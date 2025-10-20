import React from 'react';
import { render, fireEvent } from '@testing-library/react-native';
import { Button } from '../Button';

describe('Button Component', () => {
  describe('기본 렌더링', () => {
    it('제목을 렌더링해야 함', () => {
      const { getByText } = render(
        <Button title="Click Me" onPress={() => {}} />
      );

      expect(getByText('Click Me')).toBeTruthy();
    });

    it('기본 variant는 primary여야 함', () => {
      const { getByText } = render(
        <Button title="Primary Button" onPress={() => {}} />
      );

      expect(getByText('Primary Button')).toBeTruthy();
    });
  });

  describe('클릭 이벤트', () => {
    it('클릭 시 onPress 핸들러를 호출해야 함', () => {
      const mockOnPress = jest.fn();
      const { getByText } = render(
        <Button title="Test Button" onPress={mockOnPress} />
      );

      fireEvent.press(getByText('Test Button'));

      expect(mockOnPress).toHaveBeenCalledTimes(1);
    });

    it('disabled일 때 onPress를 호출하지 않아야 함', () => {
      const mockOnPress = jest.fn();
      const { getByText } = render(
        <Button title="Disabled Button" onPress={mockOnPress} disabled />
      );

      fireEvent.press(getByText('Disabled Button'));

      expect(mockOnPress).not.toHaveBeenCalled();
    });

    it('loading일 때 제목이 표시되지 않아야 함', () => {
      const mockOnPress = jest.fn();
      const { queryByText } = render(
        <Button title="Loading Button" onPress={mockOnPress} loading />
      );

      expect(queryByText('Loading Button')).toBeNull();
    });
  });

  describe('variant 속성', () => {
    it('primary variant를 렌더링해야 함', () => {
      const { getByText } = render(
        <Button title="Primary" onPress={() => {}} variant="primary" />
      );

      expect(getByText('Primary')).toBeTruthy();
    });

    it('danger variant를 렌더링해야 함', () => {
      const { getByText } = render(
        <Button title="Danger" onPress={() => {}} variant="danger" />
      );

      expect(getByText('Danger')).toBeTruthy();
    });

    it('secondary variant를 렌더링해야 함', () => {
      const { getByText } = render(
        <Button title="Secondary" onPress={() => {}} variant="secondary" />
      );

      expect(getByText('Secondary')).toBeTruthy();
    });

    it('ghost variant를 렌더링해야 함', () => {
      const { getByText } = render(
        <Button title="Ghost" onPress={() => {}} variant="ghost" />
      );

      expect(getByText('Ghost')).toBeTruthy();
    });
  });

  describe('loading 상태', () => {
    it('loading일 때 ActivityIndicator를 표시해야 함', () => {
      const { queryByText, UNSAFE_getByType } = render(
        <Button title="Loading" onPress={() => {}} loading />
      );

      expect(queryByText('Loading')).toBeNull();
      expect(UNSAFE_getByType('ActivityIndicator' as any)).toBeTruthy();
    });

    it('loading이 false일 때 제목을 표시해야 함', () => {
      const { getByText, queryByType } = render(
        <Button title="Not Loading" onPress={() => {}} loading={false} />
      );

      expect(getByText('Not Loading')).toBeTruthy();
    });
  });

  describe('커스텀 스타일', () => {
    it('커스텀 button 스타일을 적용할 수 있어야 함', () => {
      const customStyle = { marginTop: 10 };

      const { getByText } = render(
        <Button title="Custom Style" onPress={() => {}} style={customStyle} />
      );

      expect(getByText('Custom Style')).toBeTruthy();
    });

    it('커스텀 text 스타일을 적용할 수 있어야 함', () => {
      const customTextStyle = { fontSize: 20 };

      const { getByText } = render(
        <Button
          title="Custom Text"
          onPress={() => {}}
          textStyle={customTextStyle}
        />
      );

      expect(getByText('Custom Text')).toBeTruthy();
    });
  });
});
