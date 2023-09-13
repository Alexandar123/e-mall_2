<?php 

namespace MGS\Blog\Model\Options;

use Magento\Framework\Option\ArrayInterface;

class Thumbnail implements ArrayInterface  {
     
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
        $thumb_type = $post->getThumbType();
        
        if($thumb_type == 'video') {
            $option = [
                'thumbnail-video' => [
                    'label' => 'Video',
                    'value' => 'video'
                ],
                'thumbnai-image' => [
                    'label'=> 'Image',
                    'value' => 'image'
                ],
            ];
        }
        else $option = [
                'thumbnai-image' => [
                    'label'=> 'Image',
                    'value' => 'image'
                ],
                'thumbnail-video' => [
                    'label' => 'Video',
                    'value' => 'video'
                ],
            ];
           
        return $option ;
    }
}