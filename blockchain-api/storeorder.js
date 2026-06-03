const Web3 = require("web3");
const fs = require("fs");
const path = require("path");

(async () => {
  try {
    const orderId = process.argv[2];
    const fingerprint = process.argv[3];

    if (!orderId || !fingerprint) {
      console.log(JSON.stringify({ error: "Missing arguments" }));
      return;
    }

    // Ganache RPC
    const web3 = new Web3("http://127.0.0.1:7545");
    const accounts = await web3.eth.getAccounts();

    // 🔐 Convert string → bytes32
    const hashBytes32 = web3.utils.keccak256(fingerprint);

    // Load contract artifact
    const artifact = JSON.parse(
      fs.readFileSync(
        path.join(__dirname, "../blockchain/build/contracts/OrderVerifier.json"),
        "utf8"
      )
    );

    const networkId = Object.keys(artifact.networks)[0];
    const contractAddress = artifact.networks[networkId].address;

    const contract = new web3.eth.Contract(
      artifact.abi,
      contractAddress
    );

    // 🔥 STORE ON GANACHE
    const tx = await contract.methods
      .storeOrderHash(orderId, hashBytes32)
      .send({
        from: accounts[0],
        gas: 300000
      });

    // ✅ RETURN REAL BLOCKCHAIN DATA
    console.log(JSON.stringify({
      txHash: tx.transactionHash,
      blockNumber: tx.blockNumber,
      hash: hashBytes32
    }));

  } catch (err) {
    console.log(JSON.stringify({ error: err.message }));
  }
})();
