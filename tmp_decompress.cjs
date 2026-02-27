const fs = require('fs');
const LZString = require('lz-string');

const data = fs.readFileSync('storage/logs/latest_event.txt', 'utf8');
const decompressed = LZString.decompressFromBase64(data);
console.log(decompressed);
