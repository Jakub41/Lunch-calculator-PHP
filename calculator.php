<?php

$lines = [
    '40.00 Thijs Danny,Danny,Thijs,Stefan,Den',
    '45.00 Danny Danny,Thijs,Stefan,Den',
    '36.00 Stefan Danny,Thijs,Stefan',
    '40.00 Stefan Danny,Thijs,stefan,Den',
    '40.00 Danny Danny,Thijs,Stefan,Den',
    '12.00 Stefan Thijs,Stefan,Den',
    '44.00 Danny Danny,Thijs,Stefan,Den',
    '42.40 Den Danny,Stefan,Den,Den',
    '40.00 danny Danny,Thijs,Stefan,Den',
    '50.40 Thijs Danny,Thijs,Den',
    '48.00 Den Danny,thijs,Stefan,Den',
    '84.00 Thijs Thijs,Stefan,den'
];

class Calculator
{
    private $bills = [];

    /**
    * Serves 
    * @object BillItem as
    * @param array in this function scope
    **/
    private $byLines = [];

    /**
    * The last var, in which one need. this serves 
    * the array of creditors and attendees, which was 
    * structured for using in printBill function
    **/
    private $linedByBills = [];

    /**
    * Check, if the calculate() was called, or not yet
    **/
    private $calculate = 0;

    public function __construct($bills) 
    {
        foreach ($bills as $bill_item)
        {
            /*
             * Here is creating the new object, but it will be not right to manipulate with 2 objects with just several public functions and variables
             * for choosing this way we need to have 2 different tasks to finish(1 object work with 1 task, 2nd with another)
             * or we can implement our Calculator class from BillItem to have access to some of its functions and variables, for which one will be in
             * need, if decide to have 2 objects
             *
             * I decide to use just 1 object, but not to delete the code which was here, so for accessing to some vars, I had to made vars from
             * BillItem public
             *
             * without second object the code in the construct will be:
             *
             *          $data = explode(' ', $row);
             *          $this->price = (float)$data[0];
             *          $this->paid_by = strtolower($data[1]);
             *          foreach (explode(',', $data[2]) as $debtor) {
             *
             *              $this->attendees[] = strtolower($debtor);
             *          }
             *
             */

             $this->bills[] = new BillItem($bill_item);
        }
    } 

    /**
     * Main function for printBill
     *
     * @return array
     */
     private function calculate()
     {
         $creditors = [];
         // if this function was called once,one make global @property $calculate = 1(true) to not call this function one more time
 
         // this function may called from printBill() or from printOptimizedBill functions
         $this->calculate = 1;
 
         foreach ($this->bills as $lines) {
 
             $this->byLines[] = $this->calculateLines($lines->price, $lines->paid_by, $lines->attendees);
 
             // deleting debt of creditor(@task  This list includes the name of the person who has paid)
             if (!in_array($lines->paid_by, $creditors)) {
                 $creditors[] = $lines->paid_by;
             }
         }
 
         $countLines = count($this->byLines);
 
         /*
          * One need @param count for loop. Need to check line by line from the @param $byLines
          * One can have 100 lines as array and only 2 creditors, which mean that the maximum loop need to be 100-1
          */
         $count = $countLines - 1;
 
         /*
          * loop for getting our lines like:
          *
          * [0]=>
          *   ['creditor'] =>
          *      ['debtor'] => debt
         */
         if (count($creditors) > 1 && count($creditors) != $countLines) {
             for ($i = 0; $i < $count; $i++) {
                 $this->createExcel($this->byLines);
             }
         }
 
         $count1 = count($this->byLines);
 
         for ($i = 0; $i < $count1; $i++) {
             $this->findCurrentDebt();
 
             if (empty($this->byLines)) {
                 break;
             }
         }
 
         return $this->linedByBills;
     }
 
    
     /*
     * Helper function for @param calculate()
     * This function one use in loop
     */
     private function findCurrentDebt()
     {
         $returnedValue = [];
 
         $decreased_Array = [];
         // every time in loop one get the last array from @param $byLines array
         $increasedCreditor = array_pop($this->byLines);
 
         // if one get all arrays then break
         if (empty($this->byLines)) {
             return;
         }
 
         // One make smaller array for using it in @param sumDebt() function
         foreach ($this->byLines as $list) {
             foreach ($list as $creditor => $debtors) {
                 $decreased_Array[$creditor] = $debtors;
             }
         }
 
 
         foreach ($increasedCreditor as $mainCreditor => $increasedDebtors) {
             $returnedValue = $this->sumDebt($mainCreditor, $increasedDebtors, $decreased_Array);
         }
 
         // One deleting all values with creditors, which one calculated in @param sumDebt()
         foreach ($returnedValue as $creditor => $debtor) {
             foreach ($debtor as $key => $value) {
                 unset($decreased_Array[$creditor][$key]);
             }
         }
 
         $this->byLines = [];
 
         $i = 0;
 
         foreach ($decreased_Array as $key => $value) {
             $this->byLines[$i] = [$key => $value];
 
             $i++;
         }
     }
 
     /**
      * Getting creditor whom debtors one compare with other creditors debtors and if get same than one summing or minus
      * one debt from other(one always need to get positive int)
      *
      *
      * @param $mainCreditor
      * @param $increasedDebtors
      * @param $otherCreditors
      * @return array
      */
     private function sumDebt($mainCreditor, $increasedDebtors, $otherCreditors)
     {
         $usedDebs = [];
 
         foreach ($otherCreditors as $creditors => $debtors) {
             if (array_key_exists($mainCreditor, $debtors)) {
 
                 $debt = $debtors[$mainCreditor] - $increasedDebtors[$creditors];
 
                 if ($debt > 0) {
                     $this->createAsNeed($mainCreditor, $creditors, $debt);
                 } else {
                     $this->createAsNeed($creditors, $mainCreditor, $debt * -1);
                 }
 
                 $usedDebs[$creditors][$mainCreditor] = '';
             }
         }
 
         return $usedDebs;
     }
 
     /**
      * Reorganizing the base @param $linedByBills array to may use it in printBill()
      *
      * @param $debtor
      * @param $creditor
      * @param $debt
      */
     private function createAsNeed($debtor, $creditor, $debt)
     {
         if (array_key_exists($debtor, $this->linedByBills)) {
             $this->linedByBills[$debtor][$creditor] = $debt;
         } else {
             $this->linedByBills[$debtor] = [$creditor => $debt];
         }
     }
 
     /**
      * Getting @params of @object BillItem and returning it as array like this:
      *
      * [0]=>
      *   ['creditor'] =>
      *      ['debtor'] => debt
      *
      * @param $price
      * @param $paid_by
      * @param $attendees
      *
      * @return array
      */
     private function calculateLines($price, $paid_by, $attendees)
     {
         $lines = [];
         $uniqueAttendees = [];
         $notUniqueAttendees = [];
 
         // Getting the debt of everybody
         $everyoneMustPaid = $price / count($attendees);
 
         // Finding doubles(somebody may invited someone else)
         foreach ($attendees as $everyone) {
             if (!in_array($everyone, $uniqueAttendees)) {
                 $uniqueAttendees[] = $everyone;
             } else {
                 $notUniqueAttendees[] = $everyone;
             }
         }
 
         // Deleting doubles from debtors
         foreach ($uniqueAttendees as $everyone) {
             if ($everyone == $paid_by) {
                 continue;
             }
 
             if (in_array($everyone, $notUniqueAttendees)) {
                 $lines[$paid_by][$everyone] = $everyoneMustPaid * 2;
             } else {
                 $lines[$paid_by][$everyone] = $everyoneMustPaid;
             }
         }
 
         return $lines;
     }
 
     /**
      * Helper function for calculate() function
      *
      * Getting @param $byLines array like:
      *
      *  ['debtor'] =>
      *     {'creditor'} => debt
      *
      * @param $array
      */
     private function createExcel($array)
     {
         // This function calling from loop, and one need to get last array for comparing wih other arrays
         $firstDay = array_pop($array);
         // Serving arrays here before getting it to @param $byLines
         $end_result = [];
 
         // Finding same creditors and summing that creditor debtors debt to have unique creditors at the end
         foreach ($array as $everyDay) {
             if ($this->compare_array_keys($firstDay, $everyDay)) {
                 $end_result[] = $this->sumArrayValues($firstDay, $everyDay);
 
                 $firstDay = [];
             } else {
                 $end_result[] = $everyDay;
             }
         }
 
         /*
          * If there wasn't same creditor else, one adding him with his debtors at start of array to not getting
          * It twice in @param $firstDay
         */
         if (!empty($firstDay)) {
             array_unshift($end_result, $firstDay);
         }
 
         $this->byLines = $end_result;
     }
 
     /**
      * Helper function for sum all debts of debtors
      *
      * @param $base_array
      * @param $second_array
      * @return array
      */
     private function sumArrayValues($base_array, $second_array)
     {
         $end_array = [];
         $current_creditor = '';
 
         foreach ($second_array as $creditor => $debtors) {
             $current_creditor = $creditor;
 
             foreach ($debtors as $debtor => $value) {
                 if (isset($base_array[$creditor][$debtor])) {
                     $end_array[$creditor][$debtor] = ($base_array[$creditor][$debtor] += $value);
 
                     unset($base_array[$creditor][$debtor]);
                 } else {
                     $end_array[$creditor][$debtor] = $value;
                 }
             }
         }
 
         if (isset($base_array[$current_creditor])) {
             foreach ($base_array[$current_creditor] as $debtor => $value) {
                 $end_array[$current_creditor][$debtor] = $value;
             }
         }
 
         return $end_array;
     }
 
     /**
      * Helper function for make loop more shortly
      *
      * @param $base_array
      * @param $second_array
      *
      * @return bool
      */
     private function compare_array_keys($base_array, $second_array)
     {
         foreach ($base_array as $key => $value) {
             return array_key_exists($key, $second_array);
         }
 
         return false;
     }
 
     /**
      * Function for Bonus task
      *
      * one can use this with printBill function, or without
      *
      */
     public function printOptimizedBill()
     {
         // If one didn't use printBill() function that one need to organize our lines as we need them
         if(!$this->calculate){
             $this->calculate();
         }
 
         // One need to get array from end don't lose its $key
         $oneDebtor = array_slice($this->linedByBills, -1, 1);
         array_pop($this->linedByBills);
 
         $count = count($this->linedByBills);
 
         for ($i = 0; $i < $count; $i++) {
 
             if ($i > 0) {
                 $oneDebtor = array_slice($this->linedByBills, -1, 1);
                 array_pop($this->linedByBills);
             }
 
             foreach ($this->linedByBills as $debtor => $creditors) {
                 foreach ($creditors as $key => $value) {
                     if (empty($value)) {
                         unset($this->linedByBills[$debtor]);
                     }
                 }
 
                 foreach ($oneDebtor as $mainDebtor => $mainCreditors) {
                     $this->checkSame($mainDebtor, $debtor, $mainCreditors, $creditors);
                 }
             }
         }
 
         foreach ($this->linedByBills as $debtor => $lines) {
             $debtor = ucfirst($debtor);
 
             foreach ($lines as $creditor => $amount) {
 
                 $amount = number_format($amount, 2);
                 $creditor = ucfirst($creditor);
                 // Output printOptimizedBill()
                 echo "$debtor pays $creditor $amount" . PHP_EOL;
             }
         }
     }
 
     /**
      * Helper function for printOptimizedBill()
      *
      * Using this function in loop
      *
      * @param $mainDebtor
      * @param $debtor
      * @param $mainCreditors
      * @param $creditors
      */
     private function checkSame($mainDebtor, $debtor, $mainCreditors, $creditors)
     {
         foreach ($creditors as $creditor => $value) {
             if (array_key_exists($creditor, $mainCreditors) && array_key_exists($debtor, $mainCreditors)) {
                 if ($mainCreditors[$debtor] > $value) {
 
                     $this->linedByBills = array_reverse($this->linedByBills);
                     $this->linedByBills[$mainDebtor][$debtor] = ($mainCreditors[$debtor] -= $value);
                     $this->linedByBills[$mainDebtor][$creditor] = ($mainCreditors[$creditor] += $value);
                     unset($this->linedByBills[$debtor][$creditor]);
                     $this->linedByBills[$mainDebtor] = $this->linedByBills[$mainDebtor] + $mainCreditors;
                     $this->linedByBills = array_reverse($this->linedByBills);
 
                     break;
                 } else {
                     $this->linedByBills = array_reverse($this->linedByBills);
                     $this->linedByBills[$debtor][$creditor] = ($value - $mainCreditors[$debtor]);
                     $this->linedByBills[$mainDebtor][$creditor] = ($mainCreditors[$debtor] += $mainCreditors[$creditor]);
                     unset($mainCreditors[$debtor]);
                     $this->linedByBills[$mainDebtor] = $this->linedByBills[$mainDebtor] + $mainCreditors;
                     $this->linedByBills = array_reverse($this->linedByBills);
 
                     break;
                 }
             }
         }
     }
 
     public function printBill()
     {
         $payout = $this->calculate();
 
         foreach ($payout as $debtor => $lines) {
             $debtor = ucfirst($debtor);
 
             foreach ($lines as $creditor => $amount) {
 
                 $amount = number_format($amount, 2);
                 $creditor = ucfirst($creditor);
                 // Output printBill()
                 echo "$debtor pays $creditor $amount" . PHP_EOL;
             }
         }
     }
 }
 
 class BillItem
 {
 
     public $price;
     public $paid_by;
     public $attendees = [];
 
     public function __construct($row)
     {
 
         $data = explode(' ', $row);
         $this->price = (float)$data[0];
         $this->paid_by = strtolower($data[1]);
         foreach (explode(',', $data[2]) as $debtor) {
 
             $this->attendees[] = strtolower($debtor);
         }
     }
 }
 
 
 $Calculator = new Calculator($lines);
 
 // One can use just one of functions below, or both of them
 
 echo "<pre>";
 echo "printBill()";
 
 $Calculator->printBill();
 
 echo PHP_EOL;
 echo "printOptimizedBill()";
 $Calculator->printOptimizedBill();
