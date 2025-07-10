// filepath: iron-code-skins/webapp-audit/src/crypto/verification.js
const crypto = require('crypto');

/**
 * Generates a SHA-256 hash of the given data.
 * @param {string} data - The data to hash.
 * @returns {string} - The resulting hash in hexadecimal format.
 */
function generateHash(data) {
    return crypto.createHash('sha256').update(data).digest('hex');
}

/**
 * Verifies if the provided hash matches the hash of the given data.
 * @param {string} data - The original data.
 * @param {string} hash - The hash to verify against.
 * @returns {boolean} - True if the hashes match, false otherwise.
 */
function verifyHash(data, hash) {
    const computedHash = generateHash(data);
    return computedHash === hash;
}

/**
 * Generates a digital signature for the given data using a private key.
 * @param {string} data - The data to sign.
 * @param {string} privateKey - The private key for signing.
 * @returns {string} - The resulting signature in base64 format.
 */
function signData(data, privateKey) {
    const sign = crypto.createSign('SHA256');
    sign.update(data);
    return sign.sign(privateKey, 'base64');
}

/**
 * Verifies a digital signature against the original data using a public key.
 * @param {string} data - The original data.
 * @param {string} signature - The signature to verify.
 * @param {string} publicKey - The public key for verification.
 * @returns {boolean} - True if the signature is valid, false otherwise.
 */
function verifySignature(data, signature, publicKey) {
    const verify = crypto.createVerify('SHA256');
    verify.update(data);
    return verify.verify(publicKey, signature, 'base64');
}

module.exports = {
    generateHash,
    verifyHash,
    signData,
    verifySignature
};