const Web3 = require("web3");
const contractJson = require("./build/contracts/AgriChain.json");

const web3 = new Web3("http://127.0.0.1:7545");

(async () => {
  const orderId = process.argv[2];

  const contract = new web3.eth.Contract(
    contractJson.abi,
    contractJson.networks["5777"].address
  );

  const hash = await contract.methods.getOrderHash(orderId).call();
  console.log(hash);
})();
