# 上恩資訊第五點問題

## 針對以下範例程式，協助 Code Review 下訂單功能
**表達出「高內聚、低耦合、職責分離」的程式架構**

```php
<?php

class OrderProcessor {
    public function __construct(BillerInterface $biller)
    {
        $this->biller = $biller;
    }

    public function process(Order $order)
    {
        $recent = $this->getRecentOrderCount($order);

        if ($recent > 0)
        {
            throw new Exception('Duplicate order likely.');
        }

        $this->biller->bill($order->account->id, $order->amount);

        DB::table('orders')->insert(array(
            'account' => $order->account->id,
            'amount' => $order->amount;
            'created_at' => Carbon::now();
        ));
    }

    protected function getRecentOrderCount(Order $order)
    {
        $timestamp = Carbon::now()->subMinutes(5);

        return DB::table('orders')
        ->where('account', $order->account->id)
        ->where('created_at', '>=', $timestamps)
        ->count();
    }

}

```

### 題目分析

**首先可以針對，下訂單這個功能內，有兩個職責，處理訂單與判斷訂單是否重複，
依照此思路來說可以先將getRecentOrderCount 與process中寫入訂單這部分獨立出來，寫成讀取資料庫的類別，OrderRepository.php，這時候分成兩個類別，分別是OrderProcessor.php與OrderRepository.php**

**OrderRepository**

```php
<?php
class OrderRepositoriy 
{
    public function getRecentOrderCount(Order $order)
    {
        $timestamp = Carbon::now()->subMinutes(5);

        return DB::table('orders')
        ->where('account', $order->account->id)
        ->where('created_at', '>=', $timestamps)
        ->count();
    }

    public function createOrder(Order $order) 
    {
        DB::table('orders')->insert(array(
            'account' => $order->account->id,
            'amount' => $order->amount;
            'created_at' => Carbon::now();
        ));
    }
}


```
**需要將此類別注入，因此OrderProcess將改寫成以下方式**

```php
<?php

class OrderProcessor {
    public function __construct(BillerInterface $biller, OrderRepositoriy $orders)
    {
        $this->biller = $biller;
        $this->orders = $orders;
    }

    public function process(Order $order)
    {
        $recent = $this->orders->getRecentOrderCount($order);

        if ($recent > 0)
        {
            throw new Exception('Duplicate order likely.');
        }

        $this->biller->bill($order->account->id, $order->amount);

        $this->orders->createOrder($order);
    }

}
```

**依照上方判斷訂單是否有重複，此驗證後續有任何變動或新加入驗證方法的話，我們需要額外新增驗證方法，導致違反開放封閉原則，
對程式碼開放擴充，但對修改是封閉的，因此我們將驗證的部分提領出來，先將驗證方法使用Interface來做接口**

```php
<?php

interface OrderVaildatorInterface
{
    public function validator(Order $order);
}
```

**實作class RecentOrderValidator 來實現驗證部分**

```php
<?php

class RecentOrderValidator implements OrderVaildatorInterface
{
    public function __construct(OrderRepositoriy $orders)
    {
        $this->orders = $orders;
    }

    public function validator(Order $order)
    {
        $recent = $this->orders->getRecentOrderCount($order);
        if ($recent > 0)
        {
            throw new Exception('Duplicate order likely.');
        }
    }
}

```

**這時候我們實作一個驗證訂單是否超出訂購金額**

```php
<?php

class OrderOverflowValidator implements OrderVaildatorInterface
{
 

    public function validator(Order $order)
    {
        if ($this->order->amount->isOverflow())
        {
            throw new Exception('The Order amount is overflow.');
        }
    }
}
```

**這時候將OrderProcess的類別注入驗證部分，因此注入驗證方法可以為多種驗證器，以下為更改過後的程式碼**

```php
<?php

class OrderProcessor {
    public function __construct(BillerInterface $biller, OrderRepositoriy $orders, array $validates = [])
    {
        $this->biller = $biller;
        $this->orders = $orders;
        $this->validates = $validates;
    }

    public function process(Order $order)
    {
        
        foreach ($this->validates as $validate) {
            $validate->validate($order);
        }

        $this->biller->bill($order->account->id, $order->amount);

        $this->orders->createOrder($order);
    }

}
```

**這時候再將Service Providers 中註冊 OrderProcessor**

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('OrderProcessor', function ($app) 
        {
            return new OrderProcessor(
                $app->make('BillerInterface'),
                $app->make('OrderRepository'),
                [
                    $app->make('RecentOrderValidator'),
                    $app->make('OrderOverflowValidator')
                ]
            );
        });
    }

    public function boot()
    {
        // TODO Something
    }
}
```

**後續針對OrderProcessor的類別進行測試，以保護因後續需求情境改變，增加Unit Test**

