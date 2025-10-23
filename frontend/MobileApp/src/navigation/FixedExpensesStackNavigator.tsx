/**
 * 고정지출 관리 Stack Navigator
 *
 * 고정지출 관련 화면들을 관리하는 네비게이터입니다.
 */

import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { FixedExpensesScreen } from '../screens/FixedExpensesScreen';
import { AddFixedExpenseScreen } from '../screens/AddFixedExpenseScreen';
import { EditFixedExpenseScreen } from '../screens/EditFixedExpenseScreen';
import { FixedExpenseDetailScreen } from '../screens/FixedExpenseDetailScreen';
import { colors } from '../theme';

export type FixedExpensesStackParamList = {
  FixedExpensesList: undefined;
  AddFixedExpense: undefined;
  EditFixedExpense: { expenseId: number };
  FixedExpenseDetail: { expenseId: number };
};

const Stack = createNativeStackNavigator<FixedExpensesStackParamList>();

export const FixedExpensesStackNavigator: React.FC = () => {
  return (
    <Stack.Navigator
      screenOptions={{
        headerStyle: {
          backgroundColor: colors.primary,
        },
        headerTintColor: colors.cardBackground,
        headerTitleStyle: {
          fontWeight: 'bold',
        },
      }}
    >
      <Stack.Screen
        name="FixedExpensesList"
        component={FixedExpensesScreen}
        options={{
          title: '고정지출',
          headerShown: false,
        }}
      />
      <Stack.Screen
        name="AddFixedExpense"
        component={AddFixedExpenseScreen}
        options={{
          title: '고정지출 추가',
        }}
      />
      <Stack.Screen
        name="EditFixedExpense"
        component={EditFixedExpenseScreen}
        options={{
          title: '고정지출 수정',
        }}
      />
      <Stack.Screen
        name="FixedExpenseDetail"
        component={FixedExpenseDetailScreen}
        options={{
          title: '고정지출 상세',
        }}
      />
    </Stack.Navigator>
  );
};
