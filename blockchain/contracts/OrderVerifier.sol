// SPDX-License-Identifier: MIT
pragma solidity ^0.8.0;

contract OrderVerifier {
    mapping(uint256 => bytes32) public proofs;
    event OrderStored(uint256 indexed orderId, bytes32 hash);

    function storeOrderHash(uint256 orderId, bytes32 hash) public {
        proofs[orderId] = hash;
        emit OrderStored(orderId, hash);
    }
}
