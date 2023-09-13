<?php 

namespace MGS\Blog\Model\Options ;

use Magento\Framework\Option\ArrayInterface;

class Image implements ArrayInterface {
    protected $data;

    protected $request;

    function __construct(
        \MGS\Blog\Model\Post $collection,
        \Magento\Framework\App\Request\Http $request
    )
    {
        $this->data = $collection;
        $this->request = $request;
    }

    function toOptionArray()
    {
        $post_id = $this->request->getParam('post_id');
        $post = $this->data->load($post_id);
        $image_type = $post->getImageType();
        
        if($image_type == 'video') {
            $option = [
                'thumbnail-video' => [
                    'label' => __('Video'),
                    'value' => 'video'
                ],
                'thumbnai-image' => [
                    'label'=> __('Image'),
                    'value' => 'image'
                ]
            ];
        }
        else $option = [
                'image' => [
                    'label'=> __('Image'),
                    'value' => 'image'
                ],
                'video' => [
                    'label' => __('Video'),
                    'value' => 'video'
                ],
            ];
           
        return $option ;
    }
}