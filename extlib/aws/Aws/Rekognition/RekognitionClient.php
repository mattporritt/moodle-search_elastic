<?php
namespace Aws\Rekognition;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Rekognition** service.
 * @method \Aws\Result compareFaces(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise compareFacesAsync(array $args = [])
 * @method \Aws\Result createCollection(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createCollectionAsync(array $args = [])
 * @method \Aws\Result deleteCollection(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteCollectionAsync(array $args = [])
 * @method \Aws\Result deleteFaces(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteFacesAsync(array $args = [])
 * @method \Aws\Result detectFaces(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise detectFacesAsync(array $args = [])
 * @method \Aws\Result detectLabels(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise detectLabelsAsync(array $args = [])
 * @method \Aws\Result indexFaces(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise indexFacesAsync(array $args = [])
 * @method \Aws\Result listCollections(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listCollectionsAsync(array $args = [])
 * @method \Aws\Result listFaces(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listFacesAsync(array $args = [])
 * @method \Aws\Result searchFaces(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise searchFacesAsync(array $args = [])
 * @method \Aws\Result searchFacesByImage(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise searchFacesByImageAsync(array $args = [])
 */
class RekognitionClient extends AwsClient {}
