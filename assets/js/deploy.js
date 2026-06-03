const hre = require("hardhat");

async function main() {

  const OrderVerifier = await hre.ethers.getContractFactory("OrderVerifier");
  const contract = await OrderVerifier.deploy();

  await contract.deployed();

  console.log("CONTRACT_ADDRESS:", contract.address);
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
