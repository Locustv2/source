# Mutex (Mutual Exclusion)

When working on or developing multi-threaded applications, you will frequently come across situations where the same resources (such as file access) need to be shared by different threads. But imagine if `threadA` is using `resourceX` and made some modifications to it but has not saved those modifications yet. And at the same time `threadB` started to use `resourceX` making some modifications as well. Whoever saves its changes first will get them overridden by the next thread save action.

One way to prevent this from happenning is to use some kind of locking mechanism so that the resource can be used by only one thread. A mutex is a programming concept that is used to protect shared resources from being simultaneously accessed by multiple threads.


## How does mutex works?
In computer programming, mutex is implemented as an object known as a mutual exclusion object. It is basically an object that is created so that multiple threads can take turns sharing the same resource.

Threads that want to access a particular resource will have to check if the resource is available and then locks it until it is no longer required. When a resource is locked, a thread needing the resource will be queued until the resource is unlocked by the mutex.


## Implementing a mutex
As per the above explanations, we now understand that a mutex should have a locking and unlocking mechanism to protect a resource.
