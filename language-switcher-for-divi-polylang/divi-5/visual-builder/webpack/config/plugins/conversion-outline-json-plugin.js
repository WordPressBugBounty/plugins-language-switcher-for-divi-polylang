/* eslint-disable class-methods-use-this */
const jscodeshift = require('jscodeshift');
const fsp = require('fs').promises;
const path = require('path');
const glob = require('glob');
const extractStaticProperties = require('./extract-static-properties');

/**
 * Generates conversion-outline.json from conversion-outline.js source files.
 */
class ConversionOutlineJsonPlugin {
  apply(compiler) {
    compiler.hooks.beforeCompile.tapAsync(
      'ConversionOutlineJsonPlugin',
      (params, callback) => {
        const searchPattern = 'src/modules/**/conversion-outline.*';

        try {
          const files = glob.sync(searchPattern, { cwd: compiler.context });

          Promise.all(
            files.map(async (relativeFilePath) => {
              const fullFilePath = path.join(compiler.context, relativeFilePath);
              const source = await fsp.readFile(fullFilePath, 'utf8');
              const root = jscodeshift.withParser('babel')(source);

              const outlineCollection = root
                .find(jscodeshift.VariableDeclarator)
                .filter(
                  (astPath) => astPath.value.id.name === 'conversionOutline'
                );

              if (0 === outlineCollection.size()) {
                return;
              }

              const init = outlineCollection.get().node.init;
              if (!init || 'ObjectExpression' !== init.type) {
                return;
              }

              const conversionOutlineProperties = extractStaticProperties(
                init.properties
              );

              const conversionOutlineJson = {
                _comment:
                  '!!! THIS IS AN AUTOMATICALLY GENERATED FILE - DO NOT EDIT !!!',
                ...conversionOutlineProperties,
              };

              const jsonContent = JSON.stringify(conversionOutlineJson, null, 2);
              const outputPath = path.join(
                path.dirname(fullFilePath),
                'conversion-outline.json'
              );

              let shouldWrite = true;
              try {
                const existing = await fsp.readFile(outputPath, 'utf8');
                if (existing === jsonContent) {
                  shouldWrite = false;
                }
              } catch {
                // Missing file: write.
              }

              if (shouldWrite) {
                await fsp.writeFile(outputPath, jsonContent);
              }
            })
          )
            .then(() => callback())
            .catch((error) => callback(error));
        } catch (error) {
          callback(error);
        }
      }
    );
  }
}

module.exports = ConversionOutlineJsonPlugin;
