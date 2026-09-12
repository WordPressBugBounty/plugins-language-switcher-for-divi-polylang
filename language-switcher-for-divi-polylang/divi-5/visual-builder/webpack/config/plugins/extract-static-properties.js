/* eslint-disable no-undefined */
/* eslint-disable no-use-before-define */
const { reduce, map, isUndefined, filter } = require('lodash');

module.exports = function extractStaticProperties(properties) {
  function firstDefined(values) {
    return values.find(val => val !== undefined);
  }

  function extractLiteralValue(element) {
    const validLiteralTypes = [
      'StringLiteral',
      'NumericLiteral',
      'BooleanLiteral',
      'NullLiteral',
      'Literal',
    ];
    const isLiteral = validLiteralTypes.includes(element.type);
    return isLiteral ? element.value : undefined;
  }

  function extractObjectProperties(element) {
    const isObjectExpression = 'ObjectExpression' === element.type;
    return isObjectExpression ? extractStaticProperties(element.properties) : undefined;
  }

  function extractArrayValues(element) {
    const isArrayExpression = 'ArrayExpression' === element.type;
    return isArrayExpression ? extractArrayElements(element.elements) : undefined;
  }

  function extractTranslationValue(element) {
    const isTranslationCall = 'CallExpression' === element.type && '__' === element.callee.name;
    if (isTranslationCall) {
      const firstArg = element.arguments[0];
      return firstArg && 'StringLiteral' === firstArg.type ? firstArg.value : undefined;
    }
    return undefined;
  }

  function extractIdentifierValue(element) {
    const isIdentifier = 'Identifier' === element.type;
    return isIdentifier ? element.name : undefined;
  }

  function extractArrayElements(elements) {
    const extractedElements = map(elements, element => {
      const possibleValues = [
        extractLiteralValue(element),
        extractObjectProperties(element),
        extractArrayValues(element),
        extractTranslationValue(element),
        extractIdentifierValue(element),
      ];
      return firstDefined(possibleValues);
    });
    return filter(extractedElements, val => val !== undefined);
  }

  function extractValue(value) {
    const possibleValues = [
      extractLiteralValue(value),
      extractObjectProperties(value),
      extractArrayValues(value),
      extractTranslationValue(value),
      extractIdentifierValue(value),
    ];
    return firstDefined(possibleValues);
  }

  const staticProperties = reduce(properties, (acc, prop) => {
    const key = 'Identifier' === prop?.key?.type ? prop?.key?.name : prop?.key?.value;

    if (isUndefined(key)) {
      return acc;
    }

    const extractedValue = extractValue(prop.value);

    return isUndefined(extractedValue) ? acc : { ...acc, [key]: extractedValue };
  }, {});

  return staticProperties;
};
