/**
 * Asks for a secret on the terminal without echoing it (the release signer types it; it never goes to a file, a
 * command line or an environment variable).
 */
function askHidden(question) {
    return new Promise((resolve, reject) => {
        const stdin = process.stdin;
        if (!stdin.isTTY) {
            reject(new Error('A passphrase can only be typed in an interactive terminal'));
            return;
        }
        process.stdout.write(question);
        stdin.setRawMode(true);
        stdin.resume();
        stdin.setEncoding('utf8');
        let value = '';
        const onData = (chunk) => {
            // a pasted passphrase arrives as one chunk
            for (const char of chunk) {
                if (char === '\r' || char === '\n' || char === '\u0004') {
                    stdin.setRawMode(false);
                    stdin.pause();
                    stdin.removeListener('data', onData);
                    process.stdout.write('\n');
                    resolve(value);
                    return;
                } else if (char === '\u0003') {
                    process.stdout.write('\n');
                    process.exit(130);
                } else if (char === '\u007f' || char === '\b') {
                    value = value.slice(0, -1);
                } else {
                    value += char;
                }
            }
        };
        stdin.on('data', onData);
    });
}

module.exports = { askHidden };
