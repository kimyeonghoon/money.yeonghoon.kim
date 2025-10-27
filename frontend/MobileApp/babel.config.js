module.exports = function(api) {
  const isWeb = api.caller((caller) => caller?.name === 'babel-loader');

  api.cache(true);

  if (isWeb) {
    return {
      presets: [
        ['@babel/preset-env', {
          targets: { browsers: ['last 2 versions'] },
          modules: false,
        }],
        ['@babel/preset-react', { runtime: 'automatic' }],
        '@babel/preset-typescript',
      ],
    };
  }

  return {
    presets: ['module:@react-native/babel-preset'],
  };
};
