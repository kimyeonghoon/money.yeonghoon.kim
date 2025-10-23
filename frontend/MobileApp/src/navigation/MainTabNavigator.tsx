import React from 'react';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { HomeScreen } from '../screens/HomeScreen';
import { TransactionsScreen } from '../screens/TransactionsScreen';
import { BudgetScreen } from '../screens/BudgetScreen';
import { FixedExpensesScreen } from '../screens/FixedExpensesScreen';
import { SettingsScreen } from '../screens/SettingsScreen';
import { colors } from '../theme';

export type MainTabParamList = {
  Home: undefined;
  Transactions: undefined;
  Budget: undefined;
  FixedExpenses: undefined;
  Settings: undefined;
};

const Tab = createBottomTabNavigator<MainTabParamList>();

export const MainTabNavigator: React.FC = () => {
  return (
    <Tab.Navigator
      screenOptions={{
        tabBarActiveTintColor: colors.primary,
        tabBarInactiveTintColor: colors.textSecondary,
        tabBarStyle: {
          backgroundColor: colors.cardBackground,
          borderTopColor: colors.border,
          paddingBottom: 5,
          paddingTop: 5,
          height: 60,
        },
        headerStyle: {
          backgroundColor: colors.primary,
        },
        headerTintColor: colors.cardBackground,
        headerTitleStyle: {
          fontWeight: 'bold',
        },
      }}
    >
      <Tab.Screen
        name="Home"
        component={HomeScreen}
        options={{
          title: '홈',
          tabBarLabel: '홈',
        }}
      />
      <Tab.Screen
        name="Transactions"
        component={TransactionsScreen}
        options={{
          title: '거래내역',
          tabBarLabel: '거래내역',
        }}
      />
      <Tab.Screen
        name="Budget"
        component={BudgetScreen}
        options={{
          title: '예산',
          tabBarLabel: '예산',
        }}
      />
      <Tab.Screen
        name="FixedExpenses"
        component={FixedExpensesScreen}
        options={{
          title: '고정지출',
          tabBarLabel: '고정지출',
        }}
      />
      <Tab.Screen
        name="Settings"
        component={SettingsScreen}
        options={{
          title: '설정',
          tabBarLabel: '설정',
        }}
      />
    </Tab.Navigator>
  );
};
