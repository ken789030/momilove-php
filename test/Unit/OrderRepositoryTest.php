<?php


class OrderRepositoryTest extends TestCase
{

    protected $repository = null;
    


    

    public function setUp(): void
    {
        parent::setUp();
        // 這邊需要設定資料庫初始資料，避免影響到各自測試的資料 ....

        // 設定需要測試的repository

        $this->repository = new OrderRepositoriy();
    }

    public function tearDown(): void
    {
        // 這裡需要將資料庫去做 reset的動作 
    }

    /**
     * 
     * 測試 建立訂單成功  先將驗證功能忽略，能夠先測試bill 與 createOrder
     * 
     */
    public function testGetRecentOrderCount()
    {
        // arrange
        $fakeOrder = new Order();
        $fakeOrder->setAccount('ken');
        $fakeOrder->setAmount('100');
        $expected = 0;

        // act

        $actual = $this->repository->getRecentOrderCount($fakeOrder);

        // assert
        
        $this->assertEquals($expected, $actual);
    }

}