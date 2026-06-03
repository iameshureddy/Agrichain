const Web3 = require("web3");
const express = require("express");
const bodyParser = require("body-parser");

const app = express();
app.use(bodyParser.json());

const web3 = new Web3("http://127.0.0.1:7545");

const contractABI = require("./OrderHashABI.json");
const contractAddress = "PASTE_DEPLOYED_ADDRESS_HERE";

const contract = new web3.eth.Contract(contractABI, contractAddress);

app.post("/store-hash", async (req, res) => {
  try {
    const { hash } = req.body;
    const accounts = await web3.eth.getAccounts();

    const tx = await contract.methods
      .storeOrderHash(hash)
      .send({ from: accounts[0], gas: 200000 });

    res.json({
      success: true,
      txHash: tx.transactionHash,
      blockNumber: tx.blockNumber
    });

  } catch (e) {
    res.status(500).json({ error: e.message });
  }
});

app.listen(3001, () =>
  console.log("🚀 Blockchain API running on http://localhost:3001")
);
