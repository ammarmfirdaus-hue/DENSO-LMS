import assert from 'node:assert/strict';
import test from 'node:test';
import { File } from 'node:buffer';
import { compressForumImage } from '../../src/utils/compress-forum-image.js';

const previousFile = globalThis.File;
const previousDocument = globalThis.document;
const previousBitmap = globalThis.createImageBitmap;
globalThis.File = File;

function installCanvas({ width = 4000, height = 2000, outputSize = 500_000, fail = false } = {}) {
  const draw = { width: 0, height: 0, calls: 0 };
  globalThis.createImageBitmap = async () => ({ width, height, close() {} });
  globalThis.document = {
    createElement() {
      return {
        width: 0,
        height: 0,
        getContext() { return { drawImage() {} }; },
        toBlob(callback, mime, quality) {
          draw.calls += 1;
          draw.mime = mime;
          draw.quality = quality;
          callback(fail ? null : new Blob([Buffer.alloc(outputSize)], { type: mime }));
        }
      };
    }
  };
  const createElement = globalThis.document.createElement;
  globalThis.document.createElement = tag => {
    const canvas = createElement(tag);
    canvas.getContext = () => ({ drawImage() { draw.width = canvas.width; draw.height = canvas.height; } });
    return canvas;
  };
  return draw;
}

test.after(() => {
  if (previousFile === undefined) delete globalThis.File; else globalThis.File = previousFile;
  if (previousDocument === undefined) delete globalThis.document; else globalThis.document = previousDocument;
  if (previousBitmap === undefined) delete globalThis.createImageBitmap; else globalThis.createImageBitmap = previousBitmap;
});

test('compresses large static photos to a capped WebP while preserving usable quality', async () => {
  const trace = installCanvas();
  const source = new File([Buffer.alloc(1_500_000)], 'work-area.jpg', { type: 'image/jpeg', lastModified: 1000 });
  const result = await compressForumImage(source);
  assert.notEqual(result, source);
  assert.equal(result.type, 'image/webp');
  assert.equal(result.name, 'work-area.webp');
  assert.equal(result.size, 500_000);
  assert.equal(trace.width, 1920);
  assert.equal(trace.height, 960);
  assert.equal(trace.quality, 0.88);
});

test('leaves small images and GIF animation untouched', async () => {
  const trace = installCanvas({ width: 500, height: 250 });
  const small = new File([Buffer.alloc(20_000)], 'small.png', { type: 'image/png' });
  assert.equal(await compressForumImage(small), small);
  const gif = new File([Buffer.from('GIF89a-animation')], 'motion.gif', { type: 'image/gif' });
  assert.equal(await compressForumImage(gif), gif);
  assert.equal(trace.calls, 0);
});

test('leaves animated WebP unchanged and only uses compressed output when smaller', async () => {
  installCanvas();
  const animatedBytes = Buffer.alloc(24);
  animatedBytes.write('RIFF', 0); animatedBytes.writeUInt32LE(16, 4); animatedBytes.write('WEBP', 8);
  animatedBytes.write('ANIM', 12); animatedBytes.writeUInt32LE(4, 16);
  const animated = new File([animatedBytes], 'motion.webp', { type: 'image/webp' });
  assert.equal(await compressForumImage(animated), animated);

  installCanvas({ outputSize: 1_500_001 });
  const source = new File([Buffer.alloc(1_500_000)], 'source.jpg', { type: 'image/jpeg' });
  assert.equal(await compressForumImage(source), source);
});

test('falls back to original when browser encoding fails', async () => {
  installCanvas({ fail: true });
  const source = new File([Buffer.alloc(500_000)], 'fallback.png', { type: 'image/png' });
  assert.equal(await compressForumImage(source), source);
});
