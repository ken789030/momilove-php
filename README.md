# 上恩資訊第五點問題

## 針對以下範例程式，協助 Code Review 下訂單功能
**表達出「高內聚、低耦合、職責分離」的程式架構**

```php=
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

**首先可以針對**
