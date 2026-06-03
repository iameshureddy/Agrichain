const OrderVerifier = artifacts.require("OrderVerifier");

module.exports = function (deployer) {
  deployer.deploy(OrderVerifier);
};
