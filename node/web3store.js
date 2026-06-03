const Web3 = require("web3");
const fs = require("fs");

const web3 = new Web3("http://127.0.0.1:7545");

// Ganache first account private key
const PRIVATE_KEY = "0xf26630122da7d1bc9c247a68880634d55cf504771cddc1ca131062fadb4cc125";
const ACCOUNT = web3.eth.accounts.privateKeyToAccount(PRIVATE_KEY);
web3.eth.accounts.wallet.add(ACCOUNT);

// Load contract ABI
const contractJson = JSON.parse(
  fs.readFileSync("./blockchain/build/contracts/AgriChain.json")
);

const CONTRACT_ADDRESS = "0x038439A68ac23017d5ff8bb23E6AE811fD9f99D8";
const contract = new web3.eth.Contract(
  contractJson.abi,
  CONTRACT_ADDRESS
);

// Get args from CLI
const orderId = process.argv[2];
const orderHash = process.argv[3];

(async () => {
  try {
    const tx = await contract.methods
      .storeOrderHash(orderId, orderHash)
      .send({ from: ACCOUNT.address, gas: 300000 });

    console.log(JSON.stringify({
      txHash: tx.transactionHash,
      blockNumber: tx.blockNumber
    }));
  } catch (err) {
    console.error("BLOCKCHAIN_ERROR", err.message);
  }
})();
